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

namespace littler\library;

use littler\facade\FileSystem;
use think\facade\Db;

class BackUpDatabase
{
	/**
	 * @param $tables
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	public function done($tables)
	{
		$this->generator(explode(',', $tables));

		$this->zip();
	}

	/**
	 * 创建数据文件.
	 *
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\db\exception\DataNotFoundException
	 */
	public function createDataFile(): void
	{
		$file = App::backupDirectory() . $this->table . '.sql';

		$handle = fopen($file, 'wb+');

		fwrite($handle, $begin = "BEGIN;\r\n", \strlen($begin));
		$this->createClass($this->table, $handle);
		fwrite($handle, $end = 'COMMIT;', \strlen($end));

		fclose($handle);
	}

	/**
	 * @param $tables
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\db\exception\DataNotFoundException
	 */
	protected function generator($tables): void
	{
		foreach ($tables as $table) {
			$this->table = $table;

			$this->createDataFile();
		}
	}

	/**
	 * 创建了临时模型.
	 *
	 * @param $table
	 * @param $handle
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\db\exception\DataNotFoundException
	 */
	protected function createClass($table, $handle)
	{
		$this->setUnbuffered();

		// 防止 IO 多次写入
		$buffer = [];

		// 记录中记录
		$total = Db::table($table)->count();

		$limit = 1000;

		// 生成器减少内存
		while ($total > 0) {
			$items = Db::table($table)->limit($limit)->select();

			$this->writeIn($handle, $items);

			$total -= $limit;
		}
	}

	/**
	 * sql 文件格式.
	 *
	 * @param $handle
	 * @param $datas
	 */
	protected function writeIn($handle, $datas)
	{
		$values = '';
		$sql = '';
		foreach ($datas as $data) {
			foreach ($data as $value) {
				$values .= sprintf("'%s'", $value) . ',';
			}

			$sql .= sprintf('INSERT INTO `%s` VALUE (%s);' . "\r\n", $this->table, rtrim($values, ','));
			$values = '';
		}

		fwrite($handle, $sql, strlen($sql));
	}

	/**
	 * 设置未缓存模式.
	 */
	protected function setUnbuffered()
	{
		$connections = \config('database.connections');

		$connections['mysql']['params'] = [
			\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
		];

		\config([
			'connections' => $connections,
		], 'database.connections');
	}

	/**
	 * 文件压缩.
	 *
	 * @throws \Exception
	 */
	protected function zip()
	{
		$files = FileSystem::allFiles(App::backupDirectory());

		$storePath = runtime_path('database/');

		if (! FileSystem::isDirectory($storePath)) {
			FileSystem::makeDirectory($storePath);
		}

		(new Zip())->make($storePath . 'backup.zip')->addFiles($files)->close();

		FileSystem::deleteDirectory(App::backupDirectory());
	}
}
