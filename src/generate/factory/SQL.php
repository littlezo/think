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
use littler\generate\support\Table;
use littler\generate\support\TableColumn;
use littler\generate\TableExistException;
use Phinx\Db\Adapter\AdapterInterface;

class SQL extends Factory
{
	public function done(array $params)
	{
		if (! $params['table'] ?? false) {
			throw new FailedException('table name has lost~');
		}

		$this->createTable($params);

		$this->createTableColumns($params['sql'], $params['extra']);

		$this->createTableIndex($this->getIndexColumns($params['sql']));

		return $params['table'];
	}

	/**
	 * 创建表.
	 */
	protected function createTable(array $params)
	{
		$table = new Table($params['table']);

		if ($table::exist()) {
			throw new TableExistException(sprintf('Table [%s] has been existed', $params['table']));
		}

		if (! $table::create(
			$params['extra']['primary_key'],
			$params['extra']['engine'],
			$params['extra']['comment']
		)) {
			throw new FailedException(sprintf('created table [%s] failed', $params['table']));
		}
	}

	/**
	 * 创建 columns.
	 *
	 * @param $columns
	 * @param $extra
	 */
	protected function createTableColumns($columns, $extra)
	{
		$tableColumns = [];

		foreach ($columns as $column) {
			if ($column['type'] === AdapterInterface::PHINX_TYPE_DECIMAL) {
				$tableColumn = (new TableColumn())->{$column['type']}($column['field']);
			} elseif ($column['type'] === AdapterInterface::PHINX_TYPE_ENUM || $column['type'] === AdapterInterface::PHINX_TYPE_SET) {
				$tableColumn = (new TableColumn())->{$column['type']}($column['field'], $column['default']);
			} else {
				$tableColumn = (new TableColumn())->{$column['type']}($column['field'], $column['length'] ?? 0);
			}

			if ($column['nullable']) {
				$tableColumn->setNullable();
			}

			if ($column['unsigned']) {
				$tableColumn->setUnsigned();
			}

			if ($column['comment']) {
				$tableColumn->setComment($column['comment']);
			}

			if (! $this->doNotNeedDefaultValueType($column['type'])) {
				$tableColumn->setDefault($column['default']);
			}

			$tableColumns[] = $tableColumn;
		}

		if ($extra['created_time']) {
			$tableColumns[] = $this->createCreateAtColumn();
			$tableColumns[] = $this->createUpdateAtColumn();
		}

		if ($extra['soft_delete']) {
			$tableColumns[] = $this->createDeleteAtColumn();
		}

		foreach ($tableColumns as $column) {
			if (! Table::addColumn($column)) {
				throw new FailedException('创建失败');
			}
		}
	}

	/**
	 * 创建 index.
	 *
	 * @param $indexes
	 */
	protected function createTableIndex($indexes)
	{
		$method = [
			'index' => 'addIndex',
			'unique' => 'addUniqueIndex',
			'fulltext' => 'addFulltextIndex',
		];

		foreach ($indexes as $type => $index) {
			foreach ($index as $i) {
				Table::{$method[$type]}($i);
			}
		}
	}

	/**
	 * 获取有索引的 column.
	 *
	 * @param $columns
	 */
	protected function getIndexColumns($columns): array
	{
		$index = [];

		foreach ($columns as $column) {
			if ($column['index']) {
				$index[$column['index']][] = $column['field'];
			}
		}

		return $index;
	}

	/**
	 * 不需要默认值
	 */
	protected function doNotNeedDefaultValueType(string $type): bool
	{
		return in_array($type, [
			'blob', 'text', 'geometry', 'json',
			'tinytext', 'mediumtext', 'longtext',
			'tinyblob', 'mediumblob', 'longblob', 'enum', 'set',
			'date', 'datetime', 'time', 'timestamp', 'year',
		], true);
	}

	/**
	 * 创建时间.
	 */
	protected function createCreateAtColumn(): \think\migration\db\Column
	{
		return (new TableColumn())->int('created_time', 10)
			->setUnsigned()
			->setDefault(0)
			->setComment('创建时间');
	}

	/**
	 * 更新时间.
	 */
	protected function createUpdateAtColumn(): \think\migration\db\Column
	{
		return (new TableColumn())->int('updated_time', 10)
			->setUnsigned()->setDefault(0)->setComment('更新时间');
	}

	/**
	 * 软删除.
	 */
	protected function createDeleteAtColumn(): \think\migration\db\Column
	{
		return (new TableColumn())->int('deleted_time', 10)
			->setUnsigned()->setDefault(0)->setComment('软删除字段');
	}
}
