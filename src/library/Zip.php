<?php

declare(strict_types=1);
/**
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。.
 *
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

class Zip
{
    const EXTENSION = 'zip';

    protected $zipArchive;

    protected $folder;

    public function __construct()
    {
        $this->zipArchive = new \ZipArchive();
    }

    /**
     * zip 文件.
     *
     * @param $zip
     * @param null $flags
     * @throws \Exception
     * @return $this
     */
    public function make($zip, $flags = null)
    {
        if (FileSystem::extension($zip) != self::EXTENSION) {
            throw new \Exception('make zip muse set [zip] extension');
        }

        $this->zipArchive->open($zip, $flags ?: \ZipArchive::CREATE);

        return $this;
    }

    /**
     * 添加文件.
     *
     * @param $files
     * @param bool $relative
     * @return $this
     */
    public function addFiles($files, $relative = true)
    {
        if ($relative) {
            foreach ($files as $file) {
                $this->zipArchive->addFile($file->getPathname(), $this->folder . $file->getRelativePathname());
            }
        } else {
            foreach ($files as $file) {
                $this->zipArchive->addFile($file->getPathname(), $this->folder . $file->getPathname());
            }
        }

        return $this;
    }

    /**
     * 根目录.
     *
     * @return $this
     */
    public function folder(string $folder)
    {
        $this->zipArchive->addEmptyDir($folder);

        $this->folder = $folder . DIRECTORY_SEPARATOR;

        return $this;
    }

    /**
     * 解压到.
     *
     * @param $path
     * @throws \Exception
     * @return $this
     */
    public function extractTo($path)
    {
        if (! $this->zipArchive->extractTo($path)) {
            throw new \Exception('extract failed');
        }

        return $this;
    }

    public function getFiles()
    {
        $this->zipArchive;
    }

    /**
     * 关闭.
     */
    public function close()
    {
        if ($this->zipArchive) {
            $this->zipArchive->close();
        }
    }
}
