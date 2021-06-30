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

trait RegisterSignal
{
	/**
	 * 管道破裂信号.
	 *
	 * @return \Closure
	 */
	public function pipeError()
	{
		return function () {
			// todo
		};
	}

	/**
	 * Register 信号.
	 */
	protected function registerSignal()
	{
		// Process::signal(SIGALRM, $this->restartProcess());

		Process::signal(SIGCHLD, $this->waitingForWorkerExit());

		Process::signal(SIGTERM, $this->smoothExit());

		Process::signal(SIGUSR2, $this->smoothReloadWorkers());

		Process::signal(SIGUSR1, $this->workerStatus());

		Process::signal(SIGPIPE, $this->pipeError());
	}

	/**
	 * 重新拉起子进程.
	 *
	 * @return \Closure
	 */
	protected function restartProcess()
	{
		return function () {
			// var_dump('alarm here');
			/*$count = count($this->process);
			if ($count < $this->staticNum) {
			$process = $this->createStaticProcess();
			$this->workerInfo($process);
			}*/
		};
	}

	/**
	 * 等待子进程退出 防止僵尸.
	 *
	 * @return \Closure
	 */
	protected function waitingForWorkerExit()
	{
		return function () {
			while ($res = Process::wait(false)) {
				if (isset($this->processes[$res['pid']])) {
					$this->unsetWorkerStatus($res['pid']);
					unset($this->processes[$res['pid']]);
				} else {
					// 临时进程数目减少一次
					--$this->temporaryNum;
				}
			}
		};
	}

	/**
	 * 注册 SIGTERM.
	 *
	 * @return \Closure
	 */
	protected function smoothExit()
	{
		return function () {
			// 发送停止信号给子进程 等待结束后自动退出
			foreach ($this->processes as $pid => $process) {
				Process::kill($pid, SIGTERM);
			}
			// 退出 master
			Process::kill($this->master_pid, SIGKILL);
		};
	}

	/**
	 * 输出 worker 的状态
	 *
	 * @return \Closure
	 */
	protected function workerStatus()
	{
		return function () {
			foreach ($this->processes as $pid => $process) {
				Process::kill($pid, SIGUSR1);
			}
			usleep(100);
			$this->saveProcessStatus();
		};
	}

	/**
	 * 平滑重启子进程.
	 *
	 * @return \Closure
	 */
	protected function smoothReloadWorkers()
	{
		return function () {
			// 使用队列， 会发生主进程往一个不存在的进程发送消息吗？
			foreach ($this->processes as $process) {
				Process::kill((int) $process['pid'], SIGTERM);
			}
		};
	}
}
