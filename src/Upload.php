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

use littler\exceptions\FailedException;
use littler\exceptions\ValidateFailedException;
use think\exception\ValidateException;
use think\facade\Filesystem;
use think\file\UploadedFile;

class Upload
{
	/**
	 * 阿里云.
	 */
	public const OSS = 'oss';

	/**
	 * 腾讯云.
	 */
	public const QCLOUD = 'qcloud';

	/**
	 * 七牛.
	 */
	public const QIQNIU = 'qiniu';

	/**
	 * 本地.
	 */
	public const LOCAL = 'local';

	/**
	 * 驱动.
	 *
	 * @var string
	 */
	protected $driver;

	/**
	 * path.
	 *
	 * @var string
	 */
	protected $path = '';

	/**
	 * upload files.
	 */
	public function upload(UploadedFile $file): string
	{
		try {
			$this->initUploadConfig();

			$path = Filesystem::disk($this->getDriver())->putFile($this->getPath(), $file);

			if ($path) {
				$url = self::getCloudDomain($this->getDriver()) . '/' . $this->getLocalPath($path);

				event('attachment', [
					'path' => $path,
					'url' => $url,
					'driver' => $this->getDriver(),
					'file' => $file,
				]);

				return $url;
			}

			throw new FailedException('Upload Failed, Try Again!');
		} catch (\Exception $exception) {
			throw new FailedException($exception->getMessage());
		}
	}

	/**
	 * 上传到 Local.
	 *
	 * @param $file
	 */
	public function toLocal($file): string
	{
		$path = Filesystem::disk(self::LOCAL)->putFile($this->getPath(), $file);

		return public_path() . $this->getLocalPath($path);
	}

	/**
	 * 多文件上传.
	 *
	 * @param $attachments
	 * @return array|string
	 */
	public function multiUpload($attachments)
	{
		if (! is_array($attachments)) {
			return $this->upload($attachments);
		}

		$paths = [];
		foreach ($attachments as $attachment) {
			$paths[] = $this->upload($attachment);
		}

		return $paths;
	}

	/**
	 * set driver.
	 *
	 * @param $driver
	 * @throws \Exception
	 * @return $this
	 */
	public function setDriver($driver): self
	{
		if (! in_array($driver, [self::OSS, self::QCLOUD, self::QIQNIU, self::LOCAL], true)) {
			throw new \Exception(sprintf('Upload Driver [%s] Not Supported', $driver));
		}

		$this->driver = $driver;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function setPath(string $path)
	{
		$this->path = $path;

		return $this;
	}

	/**
	 * 验证图片.
	 *
	 * @return $this
	 */
	public function checkImages(array $images)
	{
		try {
			validate(['image' => config('little.upload.image')])->check($images);
		} catch (ValidateException $e) {
			throw new ValidateFailedException($e->getMessage());
		}

		return $this;
	}

	/**
	 * 验证文件.
	 *
	 * @return $this
	 */
	public function checkFiles(array $files)
	{
		try {
			validate(['file' => config('little.upload.file')])->check($files);
		} catch (ValidateException $e) {
			throw new ValidateFailedException($e->getMessage());
		}

		return $this;
	}

	/**
	 * 初始化配置.
	 */
	public function initUploadConfig()
	{
		Utils::setFilesystemConfig();
	}

	/**
	 * 获取云存储的域名.
	 *
	 * @param $driver
	 * @return string
	 */
	public static function getCloudDomain($driver): ?string
	{
		$driver = \config('filesystem.disks.' . $driver);

		switch ($driver['type']) {
			case Upload::QIQNIU:
			case Upload::LOCAL:
				return $driver['domain'];
			case Upload::OSS:
				return self::getOssDomain();
			case Upload::QCLOUD:
				return $driver['cdn'];
			default:
				throw new FailedException(sprintf('Driver [%s] Not Supported.', $driver));
		}
	}

	/**
	 * 本地路径.
	 *
	 * @param $path
	 */
	protected function getLocalPath($path): string
	{
		if ($this->getDriver() === self::LOCAL) {
			$path = str_replace(root_path('public'), '', \config('filesystem.disks.local.root')) . DIRECTORY_SEPARATOR . $path;

			return str_replace('\\', '/', $path);
		}

		return $path;
	}

	/**
	 * get upload driver.
	 */
	protected function getDriver(): string
	{
		if ($this->driver) {
			return $this->driver;
		}

		return \config('filesystem.default');
	}

	/**
	 * @return string
	 */
	protected function getPath()
	{
		return $this->path;
	}

	/**
	 * @return array
	 */
	protected function data(UploadedFile $file)
	{
		return [
			'file_size' => $file->getSize(),
			'mime_type' => $file->getMime(),
			'file_ext' => $file->getOriginalExtension(),
			'filename' => $file->getOriginalName(),
			'driver' => $this->getDriver(),
		];
	}

	/**
	 * 获取 OSS Domain.
	 *
	 * @return mixed|string
	 */
	protected static function getOssDomain(): string
	{
		$oss = \config('filesystem.disks.oss');
		if ($oss['is_cname'] === false) {
			return 'https://' . $oss['bucket'] . '.' . $oss['end_point'];
		}

		return $oss['end_point'];
	}
}
