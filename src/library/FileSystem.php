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

use littler\exceptions\FiledNotFoundException;
use Symfony\Component\Finder\Finder;

class FileSystem
{
    /**
     * 文件是否存在.
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * 获取文件内容.
     *
     * @param bool $lock
     */
    public function get(string $path, $lock = false): string
    {
        if ($this->isFile($path)) {
            return $lock ? $this->sharedGet($path) : file_get_contents($path);
        }

        throw new FiledNotFoundException("File does not exist at path {$path}");
    }

    /**
     * 安全获取文件内容.
     */
    public function sharedGet(string $path): string
    {
        $contents = '';

        $handle = fopen($path, 'rb');

        if ($handle) {
            try {
                if (flock($handle, LOCK_SH)) {
                    clearstatcache(true, $path);

                    $contents = fread($handle, $this->size($path) ?: 1);

                    flock($handle, LOCK_UN);
                }
            } finally {
                fclose($handle);
            }
        }

        return $contents;
    }

    /**
     * 加载文件返回.
     *
     * @throws FiledNotFoundException
     * @return mixed
     */
    public function getRequire(string $path)
    {
        if ($this->isFile($path)) {
            return require $path;
        }

        throw new FiledNotFoundException("File does not exist at path {$path}");
    }

    /**
     * 加载文件.
     *
     * @return mixed
     */
    public function requireOnce(string $file)
    {
        require_once $file;
    }

    /**
     * hash.
     */
    public function hash(string $path): string
    {
        return md5_file($path);
    }

    /**
     * 写入文件.
     *
     * @param bool $lock
     * @return bool|int
     */
    public function put(string $path, string $contents, $lock = false)
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * 替换.
     */
    public function replace(string $path, string $content)
    {
        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;

        $tempPath = tempnam(dirname($path), basename($path));

        chmod($tempPath, 0777 - umask());

        file_put_contents($tempPath, $content);

        rename($tempPath, $path);
    }

    /**
     * 重制文件.
     *
     * @return int
     */
    public function prepend(string $path, string $data)
    {
        if ($this->exists($path)) {
            return $this->put($path, $data . $this->get($path));
        }

        return $this->put($path, $data);
    }

    /**
     * 追加文件.
     */
    public function append(string $path, string $data): int
    {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    /**
     * 设置权限.
     *
     * @param null|int $mode
     * @return mixed
     */
    public function chmod(string $path, $mode = null)
    {
        if ($mode) {
            return chmod($path, $mode);
        }

        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * 删除文件.
     *
     * @param array|string $paths
     */
    public function delete($paths): bool
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            try {
                if (! @unlink($path)) {
                    $success = false;
                }
            } catch (\ErrorException $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * 移动文件.
     */
    public function move(string $path, string $target): bool
    {
        return rename($path, $target);
    }

    /**
     * 复制文件.
     */
    public function copy(string $path, string $target): bool
    {
        return copy($path, $target);
    }

    /**
     *硬连接.
     *
     * @return mixed|void
     */
    public function link(string $target, string $link)
    {
        $isWin = strtolower(substr(PHP_OS, 0, 3)) === 'win';
        if (! $isWin) {
            return symlink($target, $link);
        }

        $mode = $this->isDirectory($target) ? 'J' : 'H';

        exec("mklink /{$mode} " . escapeshellarg($link) . ' ' . escapeshellarg($target));
    }

    /**
     * file name.
     */
    public function name(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * basename.
     */
    public function basename(string $path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * dirname.
     */
    public function dirname(string $path): string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * 文件后缀
     */
    public function extension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * 文件类型.
     */
    public function type(string $path): string
    {
        return filetype($path);
    }

    /**
     * mimeType.
     *
     * @return false|string
     */
    public function mimeType(string $path)
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * 文件大小.
     */
    public function size(string $path): int
    {
        return filesize($path);
    }

    /**
     * 获取上次文件的修改时间.
     */
    public function lastModified(string $path): int
    {
        return filemtime($path);
    }

    /**
     * 是否是文件夹.
     */
    public function isDirectory(string $directory): bool
    {
        return is_dir($directory);
    }

    /**
     *是否可读.
     */
    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    /**
     * 是否可写.
     */
    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    /**
     * 是否是文件.
     */
    public function isFile(string $file): bool
    {
        return is_file($file);
    }

    /**
     * 查找文件.
     *
     * @param int $flags
     */
    public function glob(string $pattern, $flags = 0): array
    {
        return glob($pattern, $flags);
    }

    /**
     * 查找目录所有文件.
     *
     * @param bool $hidden
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    public function files(string $directory, $hidden = false): array
    {
        return iterator_to_array(
            Finder::create()->files()->ignoreDotFiles(! $hidden)->in($directory)->depth(0)->sortByName(),
            false
        );
    }

    /**
     * 递归文件目录下所有文件.
     *
     * @param bool $hidden
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    public function allFiles(string $directory, $hidden = false): array
    {
        return iterator_to_array(
            Finder::create()->files()->ignoreUnreadableDirs(true)->ignoreDotFiles(! $hidden)->in($directory)->sortByName(),
            false
        );
    }

    /**
     * 文件目录下所有子目录.
     */
    public function directories(string $directory): array
    {
        $directories = [];

        foreach (Finder::create()->in($directory)->directories()->depth(0)->sortByName() as $dir) {
            $directories[] = $dir->getPathname();
        }

        return $directories;
    }

    /**
     * 创建目录.
     *
     * @param int $mode
     * @param bool $recursive
     * @param bool $force
     */
    public function makeDirectory(string $path, $mode = 0755, $recursive = false, $force = false): bool
    {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * 移动目录.
     *
     * @param bool $overwrite
     */
    public function moveDirectory(string $from, string $to, $overwrite = false): bool
    {
        if ($overwrite && $this->isDirectory($to) && ! $this->deleteDirectory($to)) {
            return false;
        }

        return @rename($from, $to) === true;
    }

    /**
     *复制目录.
     *
     * @param null|int $options
     */
    public function copyDirectory(string $directory, string $destination, $options = null): bool
    {
        if (! $this->isDirectory($directory)) {
            return false;
        }

        $options = $options ?: \FilesystemIterator::SKIP_DOTS;

        if (! $this->isDirectory($destination)) {
            $this->makeDirectory($destination, 0777, true);
        }

        $items = new \FilesystemIterator($directory, $options);

        foreach ($items as $item) {
            $target = $destination . '/' . $item->getBasename();

            if ($item->isDir()) {
                $path = $item->getPathname();

                if (! $this->copyDirectory($path, $target, $options)) {
                    return false;
                }
            } else {
                if (! $this->copy($item->getPathname(), $target)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 删除目录.
     *
     * @param bool $preserve
     */
    public function deleteDirectory(string $directory, $preserve = false): bool
    {
        if (! $this->isDirectory($directory)) {
            return false;
        }

        $items = new \FilesystemIterator($directory);

        foreach ($items as $item) {
            if ($item->isDir() && ! $item->isLink()) {
                $this->deleteDirectory($item->getPathname());
            } else {
                $this->delete($item->getPathname());
            }
        }

        if (! $preserve) {
            @rmdir($directory);
        }

        return true;
    }

    /**
     * 删除目录下所有目录.
     */
    public function deleteDirectories(string $directory): bool
    {
        $allDirectories = $this->directories($directory);

        if (! empty($allDirectories)) {
            foreach ($allDirectories as $directoryName) {
                $this->deleteDirectory($directoryName);
            }

            return true;
        }

        return false;
    }

    /**
     * 清空目录.
     */
    public function cleanDirectory(string $directory): bool
    {
        return $this->deleteDirectory($directory, true);
    }
}
