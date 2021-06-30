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

namespace littler;

use think\helper\Str;
use think\Paginator;

class Query extends \think\db\Query
{
	/**
	 * @param mixed $model
	 */
	public function lzJoin($model, string $joinField, string $currentJoinField, array $field = [], string $type = 'INNER', array $bind = []): Query
	{
		$tableAlias = null;

		if (is_string($model)) {
			$table = app($model)->getTable();
		} else {
			[$model, $tableAlias] = $model;
			$table = app($model)->getTable();
		}

		// 合并字段
		$this->options['field'] = array_merge($this->options['field'] ?? [], array_map(function ($value) use ($table, $tableAlias) {
			return ($tableAlias ?: $table) . '.' . $value;
		}, $field));
		// dd($tableAlias ? sprintf('%s %s', $table, $tableAlias) : $table, sprintf('%s.%s=%s.%s', $tableAlias ? $tableAlias : $table, $joinField, $this->getAlias(), $currentJoinField), $type, $bind);
		return $this->join($tableAlias ? sprintf('%s %s', $table, $tableAlias) : $table, sprintf('%s.%s=%s.%s', $tableAlias ? $tableAlias : $table, $joinField, $this->getAlias(), $currentJoinField), $type, $bind);
	}

	/**
	 * @param mixed $model
	 */
	public function lzLeftJoin($model, string $joinField, string $currentJoinField, array $field = [], array $bind = []): Query
	{
		return $this->lzJoin($model, $joinField, $currentJoinField, $field, 'LEFT', $bind);
	}

	/**
	 * @param mixed $model
	 */
	public function lzRightJoin($model, string $joinField, string $currentJoinField, array $field = [], array $bind = []): Query
	{
		return $this->lzJoin($model, $joinField, $currentJoinField, $field, 'RIGHT', $bind);
	}

	/**
	 * rewrite.
	 *
	 * @param array|string $field
	 * @param bool $needAlias
	 * @return $this|Query
	 */
	public function withoutField($field, $needAlias = false)
	{
		if (empty($field)) {
			return $this;
		}

		if (is_string($field)) {
			$field = array_map('trim', explode(',', $field));
		}

		// 过滤软删除字段
		if ($this->model->getDeleteAtField()) {
			if ($this->model->hasField($this->model->getDeleteAtField())) {
				$field[] = $this->model->getDeleteAtField();
			}
		}

		// 字段排除
		$fields = $this->getTableFields();
		$field = $fields ? array_diff($fields, $field) : $field;
		// dd($field);

		if (isset($this->options['field'])) {
			$field = array_merge((array) $this->options['field'], $field);
		}

		$this->options['field'] = array_unique($field);

		if ($needAlias) {
			$alias = $this->getAlias();
			$this->options['field'] = array_map(function ($field) use ($alias) {
				return $alias . '.' . $field;
			}, $this->options['field']);
		}

		return $this;
	}

	/**
	 * @param array $params
	 */
	public function lzSearch($params = []): Query
	{
		$params = empty($params) ? \request()->param() : $params;

		if (empty($params)) {
			return $this;
		}

		foreach ($params as $field => $value) {
			$method = 'search' . Str::studly($field) . 'Attr';
			// value in [null, '']
			if ($value !== null && $value !== '' && method_exists($this->model, $method)) {
				$this->model->{$method}($this, $value, $params);
			}
		}
		return $this;
	}

	/**
	 * 快速搜索.
	 *
	 * @param array $params
	 */
	public function quickSearch($params = []): Query
	{
		$requestParams = \request()->param();
		// dd($requestParams);
		if (empty($params) && empty($requestParams)) {
			return $this;
		}

		foreach ($requestParams as $field => $value) {
			// 排除不存在字段
			// echo str_replace(['start_,end_,like_,max_,min_,size,page,left_like_,right_like_,'], '', $field) . PHP_EOL;
			if (! in_array(str_replace(['start_,end_,like_,left_like_,right_like_,max_,min_,size,page'], '', $field), $this->model->field, true, )) {
				// continue;
			}
			if (isset($params[$field])) {
				// ['>', value] || value
				if (is_array($params[$field])) {
					$this->where($field, $params[$field][0], $params[$field][1]);
				} else {
					$this->where($field, $value);
				}
			} else {
				// dd($this->model->field);
				[$condition] = explode('_', $field);
				// $startPos = strpos($field, 'start_');
				// $endPos = strpos($field, 'end_');
				// 时间区间范围 start_数据库字段 & end_数据库字段
				// echo $condition . PHP_EOL;
				if ($condition === 'start') {
					if (is_timestamp($value)) {
						$this->where(str_replace('start_', '', $field), '>=', (int) $value);
					} else {
						$this->where(str_replace('start_', '', $field), '>=', strtotime($value));
					}
				} elseif ($condition === 'end') {
					if (is_timestamp($value)) {
						$this->where(str_replace('end_', '', $field), '>=', (int) $value);
					} else {
						$this->where(str_replace('end_', '', $field), '>=', strtotime($value));
					}
					// 模糊搜索
				} elseif ($condition === 'like') {
					$this->whereLike(str_replace('like_', '', $field), $value);
				// 左模糊搜索
				} elseif ($condition === 'left') {
					$this->whereLeftLike(str_replace('left_like_', '', $field), $value);
				// 右模糊搜索
				} elseif ($condition === 'right') {
					$this->whereRightLike(str_replace('right_like_', '', $field), $value);
				// 区间范围查询
				} elseif ($condition === 'max') {
					$this->where(str_replace('max_', '', $field), '<=', $value);
				} elseif ($condition === 'min') {
					$this->where(str_replace('min_', '', $field), '>=', $value);
				//等值搜索 ! in_array($operate, ['like', 'max', 'min', '%like', 'like%', 'end', 'start'])
				} elseif (($value) && ! in_array($field, ['size', 'page'], true)) {
					$this->where($field, '=', $value);
				}
			}
		}
		// dd($this);
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAlias()
	{
		return isset($this->options['alias']) ? $this->options['alias'][$this->getTable()] : $this->getTable();
	}

	/**
	 * rewrite.
	 *
	 * @param mixed $condition
	 * @param string $option
	 */
	public function whereLike(string $field, $condition, string $logic = 'AND', $option = 'both'): Query
	{
		switch ($option) {
		  case 'both':
			  $condition = '%' . $condition . '%';
			  break;
		  case 'left':
			  $condition = '%' . $condition;
			  break;
		  default:
			  $condition .= '%';
		}

		if (strpos($field, '.') === false) {
			$field = $this->getAlias() . '.' . $field;
		}

		return parent::whereLike($field, $condition, $logic);
	}

	/**
	 * @param $condition
	 */
	public function whereLeftLike(string $field, $condition, string $logic = 'AND'): Query
	{
		return $this->whereLike($field, $condition, $logic, 'left');
	}

	/**
	 * @param $condition
	 */
	public function whereRightLike(string $field, $condition, string $logic = 'AND'): Query
	{
		return $this->whereLike($field, $condition, $logic, 'right');
	}

	/**
	 * 额外的字段.
	 *
	 * @param $fields
	 */
	public function addFields($fields): Query
	{
		if (is_string($fields)) {
			$this->options['field'][] = $fields;

			return $this;
		}

		$this->options['field'] = array_merge($this->options['field'], $fields);

		return $this;
	}

	public function paginate($listRows = null, $simple = false): Paginator
	{
		if (! $listRows) {
			$limit = \request()->param('size');
			$listRows = $limit ?: BaseModel::$limit;
		}
		// dd($simple);
		return parent::paginate($listRows, $simple);
	}

	/**
	 * 默认排序.
	 *
	 * @param string $order
	 * @return $this
	 */
	public function lzOrder($order = 'desc')
	{
		if (in_array('sort', array_keys($this->getFields()), true)) {
			$this->order($this->getTable() . '.sort', $order);
		}

		if (in_array('weight', array_keys($this->getFields()), true)) {
			$this->order($this->getTable() . '.weight', $order);
		}
		// dd($this->getAutoPk());
		$this->order($this->getTable() . '.' . $this->getAutoPk(), $order);

		return $this;
	}

	/**
	 * 新增 Select 子查询.
	 *
	 * @return $this
	 */
	public function addSelectSub(callable $callable, string $as)
	{
		$this->field(sprintf('%s as %s', $callable()->buildSql(), $as));

		return $this;
	}

	/**
	 * 字段增加.
	 *
	 * @param $field
	 * @param int $amount
	 * @throws \think\db\exception\DbException
	 * @return int
	 */
	public function setInc($field, $amount = 1)
	{
		return $this->inc($field, $amount)->update();
	}

	/**
	 * 字段减少.
	 *
	 * @param $field
	 * @param int $amount
	 * @throws \think\db\exception\DbException
	 * @return int
	 */
	public function setDec($field, $amount = 1)
	{
		return $this->dec($field, $amount)->update();
	}

	public function getAutoPk()
	{
		return $this->connection->getPk($this->getTable());
	}
}
