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

use Cron\CronExpression;
use think\facade\Console;

/**
 * Class Cron.
 */
class Cron
{
	use Frequencies;

	/**
	 * crontab 表达式.
	 *
	 * @var string
	 */
	protected $expression = '* * * * *';

	/**
	 * task 任务
	 *
	 * @var string
	 */
	protected $task;

	/**
	 * console 命令.
	 *
	 * @var string
	 */
	protected $console;

	/**
	 * console 参数.
	 *
	 * @var array
	 */
	protected $arguments;

	/**
	 * 秒级支持
	 *
	 * @var int
	 */
	protected $second;

	public function __construct($name, $arguments = [])
	{
		if (is_string($name)) {
			$this->console = $name;
		}

		if (is_object($name)) {
			$this->task = $name;
		}

		$this->arguments = $arguments;
	}

	/**
	 * 运行 cron 任务
	 */
	public function run()
	{
		if ($this->console) {
			Console::call($this->console, $this->arguments);
		}

		if ($this->task && method_exists($this->task, 'run')) {
			$this->task->run();
		}
	}

	/**
	 * 是否可运行.
	 *
	 * @return bool
	 */
	public function can()
	{
		if ($this->second) {
			$now = date('s', time());

			return ($now % $this->second) == 0;
		}

		if ($this->expression) {
			$cron = CronExpression::factory($this->expression);
			return $cron->getNextRunDate(date('Y-m-d H:i:s'), 0, true)->getTimestamp() == time();
		}

		return false;
	}
}
