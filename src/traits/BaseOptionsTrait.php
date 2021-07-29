<?php

declare(strict_types=1);

/*
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。
 * ## 只要思想不滑稽，方法总比苦难多！
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @link     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 *
 */

namespace littler\traits;

use littler\library\excel\reader\Reader;
use littler\Utils;

trait BaseOptionsTrait
{
	// 分页 Limit
	public static $limit = 10;

	// 开启
	public static $enable = 1;

	// 禁用
	public static $disable = 2;

	/**
	 * 查询列表.
	 *
	 * @param mixed $paginate
	 * @return mixed
	 */
	public function getList($paginate = true)
	{
		// 不分页
		if ($paginate) {
			return $this->quickSearch()
				// ->field(true)
				->lzOrder()
				->withoutField($this->without)
				->paginate();
		}
		// 分页列表
		return $this->quickSearch()
			// ->field(true)
			->lzOrder()
			->withoutField($this->without)
			->select();
	}

	/**
	 * @return int||bool
	 */
	public function storeBy(array $data)
	{
		if ($this->allowField($this->field)->save($this->filterData($data))) {
			// dd($this);
			return (int) $this->{$this->getAutoPk()};
		}

		return false;
	}

	/**
	 * 用于循环插入.
	 *
	 * @return mixed
	 */
	public function createBy(array $data)
	{
		$model = parent::create($data, $this->field, true);

		return $model->{$this->getAutoPk()};
	}

	/**
	 * @time 2019年12月03日
	 * @param $id
	 * @param $data
	 * @param string $field
	 */
	public function updateBy($id, $data, $field = ''): bool
	{
		if (static::update($this->filterData($data), [$field ?: $this->getAutoPk() => $id], $this->field)) {
			$this->updateChildren($id, $data);

			return true;
		}

		return false;
	}

	/**
	 * @param $id
	 * @param bool $trash
	 * @return mixed
	 */
	public function findBy($id, array $field = ['*'], $trash = false)
	{
		if ($trash) {
			return static::onlyTrashed()->find($id);
		}

		return static::where($this->getAutoPk(), $id)->field($field)->withoutField($this->without)->find();
	}

	/**
	 * @param $id
	 * @param $force
	 * @return mixed
	 */
	public function deleteBy($id, $force = false)
	{
		return static::destroy(is_array($id) ? $id : Utils::stringToArrayBy((string) $id), $force);
	}

	/**
	 * 批量插入.
	 *
	 * @return mixed
	 */
	public function insertAllBy(array $data)
	{
		$newData = [];
		foreach ($data as $item) {
			foreach ($item as $field => $value) {
				if (! in_array($field, $this->field, true)) {
					unset($item[$field]);
				}

				if (in_array($this->createTime, $this->field, true)) {
					$item[$this->createTime] = time();
				}

				if (in_array($this->updateTime, $this->field, true)) {
					$item[$this->updateTime] = time();
				}
			}
			$newData[] = $item;
		}
		return $this->insertAll($newData);
	}

	/**
	 * @param $id
	 * @return mixed
	 */
	public function recover($id)
	{
		return static::onlyTrashed()->find($id)->restore();
	}

	/**
	 * 获取删除字段.
	 *
	 * @return mixed
	 */
	public function getDeleteAtField()
	{
		if ($this->hasField($this->deleteTime)) {
			return $this->deleteTime;
		}
		return null;
	}

	/**
	 * 递归更新子级.
	 *
	 * @param $parentId
	 * @param $parentIdField
	 * @param $updateData
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	public function recursiveUpdate($parentId, $parentIdField, $updateData)
	{
		$this->where($parentIdField, $parentId)->update($updateData);

		$children = $this->where($parentIdField, $parentId)->select();

		if ($children->count()) {
			foreach ($children as $child) {
				$this->recursiveUpdate($child->id, $parentIdField, $updateData);
			}
		}
	}

	/**
	 * 别名.
	 *
	 * @param $field
	 * @return array|string
	 */
	public function aliasField($field, $table='')
	{
		$table = $table ? Utils::tableWithPrefix($table) : $this->getTable();
		if (is_string($field)) {
			return sprintf('%s.%s', $table, $field);
		}

		if (is_array($field)) {
			foreach ($field as &$value) {
				$value = sprintf('%s.%s', $table, $value);
			}

			return $field;
		}

		return $field;
	}

	/**
	 * 禁用/启用.
	 *
	 * @param $id
	 * @param string $field
	 * @return mixed
	 */
	public function disOrEnable($id, $field = 'status')
	{
		$model = $this->findBy($id);

		$status = $model->{$field} == self::$disable ? self::$enable : self::$disable;

		$model->{$field} = $status;

		return $model->save();
	}

	/**
	 * 模型导入.
	 *
	 * @param $fields
	 * @param $file
	 */
	public function import($fields, $file): bool
	{
		$excel = new class(array_column($fields, 'field')) extends Reader {
			protected $fields;

			public function __construct($fields)
			{
				$this->fields = $fields;
			}

			public function headers()
			{
				// TODO: Implement headers() method.
				return $this->fields;
			}
		};

		$options = [];
		foreach ($fields as $field) {
			$p = [];
			if (isset($field['options']) && count($field['options'])) {
				foreach ($field['options'] as $op) {
					$p[$op['label']] = $op['value'];
				}
				$options[$field['field']] = $p;
			}
		}

		$excel->import($file)->remove(0)->then(function ($data) use ($options) {
			foreach ($data as &$d) {
				foreach ($d as $field => &$v) {
					if (isset($options[$field])) {
						$v = $options[$field][$v];
					}
				}
				$this->createBy($d);
			}
		});

		return true;
	}

	/**
	 * 更新下级.
	 *
	 * @param $parentId
	 * @param $data
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	protected function updateChildren($parentId, $data)
	{
		if (property_exists($this, 'updateChildrenFields')) {
			$parentIdField = property_exists($this, 'parent') ? $this->{$parentId} : 'parent';

			if (! empty($this->updateChildrenFields)) {
				if (is_array($this->updateChildrenFields)) {
					foreach ($data as $field => $value) {
						if (in_array($field, $this->updateChildrenFields, true)) {
							unset($data[$field]);
						}
					}

					$this->recursiveUpdate($parentId, $parentIdField, $data);
				}

				if (is_string($this->updateChildrenFields) && isset($data[$this->updateChildrenFields])) {
					$this->recursiveUpdate($parentId, $parentIdField, [
						$this->updateChildrenFields => $data[$this->updateChildrenFields],
					]);
				}
			}
		}
	}

	/**
	 * 过滤数据.
	 *
	 * @param $data
	 * @return mixed
	 */
	protected function filterData($data)
	{
		foreach ($data as $field => $value) {
			if (is_null($value)) {
				unset($data[$field]);
			}
			if (! in_array($field, $this->field, true)) {
				// unset($data[$field]);
			}
			if ($field == $this->getAutoPk()) {
				unset($data[$field]);
			}
		}

		return $data;
	}
}
