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

use littler\exceptions\FailedException;

class Schedule
{
	protected $crons = [];

	/**
	 * 新增 command 任务
	 *
	 * @param $command
	 * @param array $arguments
	 */
	public function command($command, $arguments = []): Cron
	{
		$this->crons[] = $cron = new Cron($command);

		return $cron;
	}

	/**
	 * 新增 task 任务
	 *
	 * @param $task
	 * @param array $argument
	 */
	public function task($task, $argument = []): Cron
	{
		if (is_string($task)) {
			if (! class_exists($task)) {
				throw new FailedException("[{$task}] not found");
			}

			$task = new $task(...$argument);
		}

		$this->crons[] = $cron = new Cron($task);

		return $cron;
	}

	public function getCronTask()
	{
		return $this->crons;
	}
}
