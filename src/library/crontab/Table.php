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

namespace littler\library\crontab;

use Swoole\Table as STable;

trait Table
{
	/**
	 * @var STable
	 */
	protected $table;

	protected function createTable()
	{
		$this->table = new STable(1024);

		$this->table->column('pid', STable::TYPE_INT, 4);       //1,2,4,8
		$this->table->column('memory', STable::TYPE_INT, 4);
		$this->table->column('start_at', STable::TYPE_INT, 8);
		$this->table->column('running_time', STable::TYPE_INT, 8);
		$this->table->column('status', STable::TYPE_STRING, 15);
		$this->table->column('deal_tasks', STable::TYPE_INT, 4);
		$this->table->column('errors', STable::TYPE_INT, 4);
		$this->table->create();
	}

	protected function addColumn($pid, $value)
	{
		return $this->table->set($pid, $value);
	}
}
