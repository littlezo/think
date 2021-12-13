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

trait Store
{
	/**
	 * 存储 pid.
	 *
	 * @param $pid
	 * @return false|int
	 */
	public function storeMasterPid($pid)
	{
		$path = $this->getMasterPidPath();

		return file_put_contents($path, $pid);
	}

	/**
	 * 输出.
	 *
	 * @return false|string
	 */
	public function output()
	{
		// 等待信号输出
		sleep(1);

		return $this->getProcessStatusInfo();
	}

	/**
	 * 获取 pid.
	 *
	 * @return int
	 */
	public function getMasterPid()
	{
		$pid = file_get_contents($this->getMasterPidPath());

		return intval($pid);
	}

	/**
	 * 清除退出的 worker 信息.
	 *
	 * @param $pid
	 */
	protected function unsetWorkerStatus($pid)
	{
		$this->table->del($this->getColumnKey($pid));
	}

	/**
	 * 获取配置地址
	 *
	 * @return string
	 */
	protected function getMasterPidPath()
	{
		return config('little.schedule.master_pid_file');
	}

	/**
	 * 创建任务调度文件夹.
	 *
	 * @return string
	 */
	protected function schedulePath()
	{
		$path = config('little.schedule.store_path');

		if (! is_dir($path)) {
			mkdir($path, 0777, true);
		}

		return $path;
	}

	/**
	 * 进程状态文件.
	 *
	 * @return string
	 */
	protected function getSaveProcessStatusFile()
	{
		return $this->schedulePath() . '.worker-status';
	}

	/**
	 *  保存进程状态
	 */
	protected function saveProcessStatus()
	{
		file_put_contents($this->getSaveProcessStatusFile(), $this->renderProcessesStatusToString());
	}

	/**
	 * 获取进程状态
	 *
	 * @return false|string
	 */
	protected function getProcessStatusInfo()
	{
		return file_get_contents($this->getSaveProcessStatusFile());
	}
}
