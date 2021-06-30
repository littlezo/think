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

use little\monitor\model\CrontabLog;

abstract class BaseCronTask
{
	protected $exceptionHappenTimes = 0;

	protected $exitTimes = 1;

	protected $crontab;

	/**
	 * @return mixed
	 */
	abstract public function deal();

	/**
	 * @return mixed
	 */
	abstract public function dealWithException(\Throwable $e);

	/**
	 * 执行.
	 *
	 * @return bool|void
	 */
	public function run()
	{
		$startAt = round(microtime(true) * 1000);
		try {
			if ($this->deal() === false) {
				return false;
			}
			$this->recordLog($startAt);
			return true;
		} catch (\Throwable $e) {
			$this->dealWithException($e);
			echo sprintf('[%s]: ', date('Y-m-d H:i:s')) . 'File:' . $e->getFile() . ', Lines: ' . $e->getLine() . '行，Exception Message: ' . $e->getMessage() . PHP_EOL;
			// 输出堆栈信息
			echo sprintf('[%s]: ', date('Y-m-d H:i:s')) . $e->getTraceAsString() . PHP_EOL;
			// 日志记录
			$this->recordLog($startAt, 'File:' . $e->getFile() . ', Lines: ' . $e->getLine() . '行，Exception Message: ' . $e->getMessage());
			++$this->exceptionHappenTimes;
		}
	}

	/**
	 * 退出.
	 *
	 * @return bool
	 */
	public function shouldExit()
	{
		// var_dump($this->exceptionHappenTimes);
		return $this->exceptionHappenTimes > $this->exitTimes;
	}

	/**
	 * 设置 crontab.
	 *
	 * @return $this
	 */
	public function setCrontab(array $crontab)
	{
		$this->crontab = $crontab;

		return $this;
	}

	protected function recordLog($startAt, $message = '')
	{
		$endAt = round(microtime(true) * 1000);
		CrontabLog::insert([
			'crontab_id' => $this->crontab['id'],
			'used_time' => $endAt - $startAt,
			'error_message' => $message,
			'status' => $message ? CrontabLog::FAILED : CrontabLog::SUCCESS,
			'created_time' => time(),
			'updated_time' => time(),
		]);
	}
}
