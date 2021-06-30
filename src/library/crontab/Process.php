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

use think\console\Table;
use think\facade\Log;

trait Process
{
	/**
	 * quit 退出.
	 *
	 * @var bool
	 */
	protected $quit = false;

	/**
	 * 设置最大内存/256M.
	 *
	 * @var [type]
	 */
	protected $maxMemory = 256 * 1024 * 1024;

	/**
	 * 退出服务
	 */
	public function stop()
	{
		\Swoole\Process::kill($this->getMasterPid(), SIGTERM);
	}

	/**
	 * 状态输出.
	 */
	public function status()
	{
		\Swoole\Process::kill($this->getMasterPid(), SIGUSR1);
	}

	/**
	 * 子进程重启.
	 */
	public function reload()
	{
		\Swoole\Process::kill($this->getMasterPid(), SIGUSR2);
	}

	/**
	 * 输出 process 信息.
	 *
	 * @return string
	 */
	public function renderProcessesStatusToString()
	{
		$scheduleV = self::VERSION;
		$adminV = App::VERSION;
		$phpV = PHP_VERSION;

		$processNumber = $this->table->count();
		$memory = (int) (memory_get_usage() / 1024 / 1024) . 'M';
		$startAt = date('Y-m-d H:i:s', $this->master_start_at);
		$runtime = gmstrftime('%H:%M:%S', time() - $this->master_start_at);
		$info = <<<EOT
			-------------------------------------------------------------------------------------------------------
			|   ____      _       _        _       _           _         ____       _              _       _       |
			|  / ___|__ _| |_ ___| |__    / \\   __| |_ __ ___ (_)_ __   / ___|  ___| |__   ___  __| |_   _| | ___  |
			| | |   / _` | __/ __| '_ \\  / _ \\ / _` | '_ ` _ \\| | '_ \\  \\___ \\ / __| '_ \\ / _ \\/ _` | | | | |/ _ \\ |
			| | |__| (_| | || (__| | | |/ ___ \\ (_| | | | | | | | | | |  ___) | (__| | | |  __/ (_| | |_| | |  __/ |
			|  \\____\\__,_|\\__\\___|_| |_/_/   \\_\\__,_|_| |_| |_|_|_| |_| |____/ \\___|_| |_|\\___|\\__,_|\\__,_|_|\\___| |
			| ----------------------------------------- App Schedule ---------------------------------------|
			|  Schedule Version: {$scheduleV}         App Version: {$adminV}         PHP Version: {$phpV}         |
			|  Process Number: {$processNumber}           Memory: {$memory}                     Start at: {$startAt}     |
			|  Running Time: {$runtime}                                                                              |
			|------------------------------------------------------------------------------------------------------|
			EOT;

		$table = new Table();
		$table->setHeader([
			'pid', 'memory', 'start_at', 'running_time', 'status', 'deal_tasks', 'errors',
		], 2);

		$processes = [];

		foreach ($this->table as $process) {
			$processes[] = [
				$process['pid'],
				(int) ($process['memory'] / 1024 / 1024) . 'M',
				date('Y-m-d H:i', $process['start_at']),
				gmstrftime('%H:%M:%S', $process['running_time']),
				$process['status'],
				$process['deal_tasks'],
				$process['errors'],
			];
		}

		$table->setRows($processes, 2);

		$table->render();

		return $info . PHP_EOL . $table->render();
	}

	/**
	 * 创建进程.
	 *
	 * @return \Closure
	 */
	protected function createProcessCallback()
	{
		return function (\Swoole\Process $process) {
			// 必须使用 pcntl signal 注册捕获
			// Swoole\Process::signal ignalfd 和 EventLoop 是异步 IO，不能用于阻塞的程序中，会导致注册的监听回调函数得不到调度
			// 同步阻塞的程序可以使用 pcntl 扩展提供的 pcntl_signal
			// 安全退出进程
			pcntl_signal(SIGTERM, function () {
				$this->quit = true;
			});

			pcntl_signal(SIGUSR1, function () use ($process) {
				// todo
				$this->updateTask($process->pid);
			});

			while (true) {
				$cron = $process->pop();
				if ($cron && is_string($cron)) {
					$cron = unserialize($cron);
					$this->beforeTask($process->pid);
					try {
						$cron->run();
					} catch (\Throwable $e) {
						$this->addErrors($process->pid);
						Log::error($e->getMessage() . ': at ' . $e->getFile() . ' ' . $e->getLine() . '行' .
							PHP_EOL . $e->getTraceAsString());
					}
					$this->afterTask($process->pid);
				}
				pcntl_signal_dispatch();
				sleep(1);

				// 超过最大内存
				if (memory_get_usage() > $this->maxMemory) {
					$this->quit = true;
				}

				// 如果收到安全退出的信号，需要在最后任务处理完成之后退出
				if ($this->quit) {
					Log::info('worker quit');
					$process->exit(0);
				}
			}
		};
	}

	/**
	 * 进程信息.
	 *
	 * @param $process
	 * @return array
	 */
	protected function processInfo($process)
	{
		return [
			'pid' => $process->pid,
			'memory' => memory_get_usage(),
			'start_at' => time(),
			'running_time' => 0,
			'status' => self::WAITING,
			'deal_tasks' => 0,
			'errors' => 0,
		];
	}

	/**
	 * 是否有等待的 Process.
	 *
	 * @return array
	 */
	protected function hasWaitingProcess()
	{
		$waiting = [false, null];

		$pid = 0;

		// $processIds
		foreach ($this->table as $process) {
			if ($process['status'] == self::WAITING) {
				$pid = $process['pid'];
				break;
			}
		}

		// 获取相应的进程投递任务
		if (isset($this->processes[$pid])) {
			return [true, $this->processes[$pid]];
		}

		return $waiting;
	}

	/**
	 * 处理任务前.
	 *
	 * @param $pid
	 */
	protected function beforeTask($pid)
	{
		if ($process = $this->table->get($this->getColumnKey($pid))) {
			$process['status'] = self::BUSYING;
			$process['running_time'] = time() - $process['start_at'];
			$process['memory'] = memory_get_usage();
			$this->table->set($this->getColumnKey($pid), $process);
		}
	}

	/**
	 * 处理任务后.
	 *
	 * @param $pid
	 */
	protected function afterTask($pid)
	{
		if ($process = $this->table->get($this->getColumnKey($pid))) {
			$process['status'] = self::WAITING;
			$process['running_time'] = time() - $process['start_at'];
			$process['memory'] = memory_get_usage();
			++$process['deal_tasks'];
			$this->table->set($this->getColumnKey($pid), $process);
		}
	}

	/**
	 * 更新信息.
	 *
	 * @param $pid
	 */
	protected function updateTask($pid)
	{
		if ($process = $this->table->get($this->getColumnKey($pid))) {
			$process['running_time'] = time() - $process['start_at'];
			$process['memory'] = memory_get_usage();
			$this->table->set($this->getColumnKey($pid), $process);
		}
	}

	/**
	 * 增加错误.
	 *
	 * @param $pid
	 */
	protected function addErrors($pid)
	{
		if ($process = $this->table->get($this->getColumnKey($pid))) {
			++$process['errors'];
			$this->table->set($this->getColumnKey($pid), $process);
		}
	}
}
