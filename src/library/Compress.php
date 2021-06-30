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

use littler\App;
use littler\exceptions\FailedException;
use littler\facade\FileSystem;
use littler\facade\Http;
use function GuzzleHttp\Psr7\stream_for;

class Compress
{
	protected $savePath;

	protected $zip;

	public function __construct()
	{
		if (! extension_loaded('zip')) {
			throw new FailedException('you should install extension [zip]');
		}
	}

	/**
	 * 压缩模块包.
	 *
	 * @param $moduleName
	 * @throws \Exception
	 * @return bool
	 */
	public function moduleToZip(string $moduleName, string $zipPath = '')
	{
		if (! is_dir(App::directory() . $moduleName)) {
			throw new FailedException(sprintf('module 【%s】not found~', $moduleName));
		}

		(new Zip())->make($zipPath ?: App::directory() . $moduleName . '.zip', \ZipArchive::CREATE)
			->folder($moduleName)
			->addFiles(FileSystem::allFiles(App::moduleDirectory($moduleName)))
			->close();

		return true;
	}

	/**
	 * download zip.
	 *
	 * @param $remotePackageUrl
	 * @return string
	 */
	public function download($remotePackageUrl = '')
	{
		$response = Http::options([
			'save_to' => stream_for(fopen($this->savePath, 'w+')),
		])
			->get($remotePackageUrl);

		return $response->ok();
	}

	/**
	 * 更新.
	 *
	 * @param $moduleName
	 * @return bool
	 */
	public function update($moduleName)
	{
		// 备份
		$backupPath = $this->backup($moduleName);
		try {
			$this->moduleUnzip($moduleName, $this->savePath);
		} catch (\Exception $exception) {
			// 更新失败先删除原目录
			FileSystem::deleteDirectory(App::moduleDirectory($moduleName));
			// 解压备份文件
			$this->moduleUnzip($moduleName, $backupPath);
			// 删除备份文件
			FileSystem::delete($backupPath);
			return false;
		}
		// 删除备份文件
		FileSystem::delete($backupPath);
		return true;
	}

	/**
	 * overwrite package.
	 *
	 * @param $moduleName
	 * @param $zipPath
	 * @throws \Exception
	 * @return bool
	 */
	public function moduleUnzip($moduleName, $zipPath)
	{
		try {
			(new Zip())->make($zipPath)->extractTo(App::moduleDirectory($moduleName) . $moduleName)->close();
			return true;
		} catch (\Exception $e) {
			throw new FailedException('更新失败');
		}
	}

	/**
	 * 删除目录.
	 *
	 * @param $packageDir
	 */
	public function rmDir($packageDir)
	{
		$fileSystemIterator = new \FilesystemIterator($packageDir);
		try {
			foreach ($fileSystemIterator as $fileSystem) {
				if ($fileSystem->isDir()) {
					if ((new \FilesystemIterator($fileSystem->getPathName()))->valid()) {
						$this->rmDir($fileSystem->getPathName());
					} else {
						rmdir($fileSystem->getPathName());
					}
				} else {
					unlink($fileSystem->getPathName());
				}
			}
		} catch (\Exception $exception) {
			throw new FailedException($exception->getMessage());
		}

		rmdir($packageDir);
	}

	/**
	 * 保存地址
	 *
	 * @param $path
	 * @return $this
	 */
	public function savePath($path)
	{
		$this->savePath = $path;

		return $this;
	}

	/**
	 * @param $path
	 * @param string $moduleName
	 * @param $tempExtractToPath
	 */
	protected function copyFileToModule($path, $moduleName, $tempExtractToPath)
	{
		$fileSystemIterator = new \FilesystemIterator($path . $moduleName ?: '');

		foreach ($fileSystemIterator as $fileSystem) {
			if ($fileSystem->isDir()) {
				$this->copyFileToModule($fileSystem->getPathname(), '', $tempExtractToPath);
			} else {
				// 原模块文件
				$originModuleFile = str_replace($tempExtractToPath, App::directory(), $fileSystem->getPathname());
				// md5 校验 文件是否修改过
				if (md5_file($originModuleFile) != md5_file($fileSystem->getPathname())) {
					if (! copy($fileSystem->getPathname(), $originModuleFile)) {
						throw new FailedException('更新失败');
					}
				}
			}
		}
	}

	/**
	 * 备份原文件.
	 *
	 * @param $moduleName
	 * @return bool
	 */
	protected function backup($moduleName)
	{
		$backup = $this->getModuleBackupPath($moduleName);

		App::makeDirectory($backup);

		$this->moduleToZip($moduleName, $backup . $moduleName . '.zip');

		return $backup . $moduleName . '.zip';
	}

	/**
	 * 获取备份地址
	 *
	 * @param $moduleName
	 * @return string
	 */
	protected function getModuleBackupPath($moduleName)
	{
		return $backup = runtime_path('module' . DIRECTORY_SEPARATOR . 'backup_' . $moduleName);
	}
}
