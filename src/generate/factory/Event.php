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
namespace littler\generate\factory;

use littler\exceptions\FailedException;
use littler\facade\FileSystem;
use littler\generate\build\Build;
use littler\generate\build\classes\Classes;
use littler\generate\build\classes\Property;
use littler\generate\build\classes\Traits;
use littler\generate\build\classes\Uses;
use littler\generate\build\types\Arr;
use littler\traits\db\BaseOptionsTrait;
use littler\traits\db\RewriteTrait;
use littler\Utils;
use think\facade\Db;
use think\helper\Str;
use think\model\concern\SoftDelete;

class Event extends Factory
{
	/**
	 * 事件生成.
	 *
	 * @param $params
	 */
	public function done(array $params): string
	{
		$contentRepository = $this->getRepositoryContent($params);
		$modelRepositoryFile = $this->getGeneratePath($params['model_repository']);
		FileSystem::put($modelRepositoryFile, $contentRepository);
		$content = $this->getContent($params);
		$modelPath = $this->getGeneratePath($params['model']);
		if (! file_exists($modelPath)) {
			FileSystem::put($modelPath, $content);
		}
		return $modelPath;
	}

	/**
	 * 查询后事件.
	 *
	 * @param $params
	 * @return string|string[]
	 */
	public function onAfterRead($params)
	{
		$extra = $params['extra'];
		$table = Utils::tableWithPrefix($params['table']);
		[$modelName, $namespace] = $this->parseFilename($params['model_repository']);
		// 如果填写了表名并且没有填写模型名称 使用表名作为模型名称
		if (! $modelName && $table) {
			$modelName = ucfirst(Str::camel($table));
			$params['model'] = $params['model'] . $modelName;
		}
		if (! $modelName) {
			throw new FailedException('model name not set');
		}
		$softDelete = $this->isSoftDelete($table) ?? $extra['soft_delete'];

		return (new Build())->namespace($namespace)
			->use((new Uses())->name('littler\BaseModel', 'Model'))
			->use((new Uses())->name(BaseOptionsTrait::class))
			->use((new Uses())->name(RewriteTrait::class))
			->when($softDelete, function (Build $build) {
				$build->use((new Uses())->name(SoftDelete::class));
			})
			->class(
				(new Classes($modelName))
					->extend('Model')
					->abstract()
					->docComment($this->buildClassComment($table)),
				function (Classes $class) use ($table, $softDelete) {
					$class->addTrait(
						(new Traits())->use('BaseOptionsTrait', 'RewriteTrait')
					);
					$class->when(
						$softDelete,
						function () use ($class) {
							$class->addTrait(
								(new Traits())->use('SoftDelete')
							);
						}
					);
					// dd($class);
					$class->addProperty(
						(new Property('name'))->default(
							Utils::tableWithoutPrefix($table)
						)->docComment('// 表名')
					);
					$class->when($this->hasTableExists($table), function ($class) use ($table) {
						$class->addProperty(
							(new Property('field'))->default(
								// dd(Db::getFields($table))
								(new Arr())->build(Db::getFields($table))
							)->docComment('// 数据库字段映射')
						);
					});
					$class->when($this->jsonField($table), function ($class) use ($table) {
						$class->addProperty(
							(new Property('json'))->default(
								$this->jsonField($table)
								// new Array_($items)
							)->docComment('// 设置json类型字段')
						);
						$class->addProperty(
							(new Property('jsonAssoc'))->default(true)->docComment('//  设置JSON数据返回数组')
						);
						// dd($this->jsonField($table));
					});
				}
			)->getContent();
	}

	/**
	 * 新增前事件.
	 *
	 * @param $table
	 */
	protected function onBeforeInsert($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 更新前事件.
	 *
	 * @param $table
	 */
	protected function onBeforeUpdate($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 更新后事件.
	 *
	 * @param $table
	 */
	protected function onAfterUpdate($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 写入前事件.
	 *
	 * @param $table
	 */
	protected function onBeforeWrite($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 写入后事件.
	 *
	 * @param $table
	 */
	protected function onAfterWrite($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 	删除前事件.
	 *
	 * @param $table
	 */
	protected function onBeforeDelete($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 删除后事件.
	 *
	 * @param $table
	 */
	protected function onAfterDelete($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 恢复前事件.
	 *
	 * @param $table
	 */
	protected function onBeforeRestore($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 	恢复后事件.
	 *
	 * @param $table
	 */
	protected function onAfterRestore($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 单条查询.
	 *
	 * @param $table
	 */
	protected function onQueryByFind($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 字段聚合查询.
	 *
	 * @param $table->type count max min avg sum
	 */
	protected function onQueryByPoly($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 字段自增.
	 *
	 * @param $table
	 */
	protected function onSetIncByField($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 字段自减.
	 *
	 * @param $table
	 */
	protected function onSetDecByField($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 字段更新.
	 *
	 * @param $table
	 */
	protected function onSetByField($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 字段值查询.
	 *
	 * @param $table
	 */
	protected function onQueryByField($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * ID 查询数据.
	 *
	 * @param $table
	 */
	protected function onGetById($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 根据id删除数据.
	 *
	 * @param $table
	 */
	protected function onDeleteById($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 根据条件 删除数据.
	 *
	 * @param $table
	 */
	protected function onDeleteByWhere($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 根据ID 更新数据.
	 *
	 * @param $table
	 */
	protected function onUpdateById($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 根据条件 更新数据.
	 *
	 * @param $table
	 */
	protected function onUpdateByWhere($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}

	/**
	 * 写入数据.
	 *
	 * @param $table
	 */
	protected function onSave($table)
	{
		$fields = Db::getFields($table);
		$items = false;
		foreach ($fields as $field => $item) {
			if ($item['type'] === 'json') {
				$items[] = $field;
			}
		}
		return $items;
	}
}
