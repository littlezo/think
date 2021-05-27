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
namespace littler\facade;

use think\Facade;

/**
 * @method static  \littler\library\FileSystem exists($path)
 * @method static  \littler\library\FileSystem sharedGet($path)
 * @method static  \littler\library\FileSystem requireOnce($file)
 * @method static  \littler\library\FileSystem hash($path)
 * @method static  \littler\library\FileSystem put($path, $contents, $lock = false)
 * @method static  \littler\library\FileSystem replace($path, $content)
 * @method static  \littler\library\FileSystem prepend($path, $data)
 * @method static  \littler\library\FileSystem append($path, $data)
 * @method static  \littler\library\FileSystem chmod($path, $mode = null)
 * @method static  \littler\library\FileSystem delete($paths)
 * @method static  \littler\library\FileSystem move($path, $target)
 * @method static  \littler\library\FileSystem copy($path, $target)
 * @method static  \littler\library\FileSystem link($target, $link)
 * @method static  \littler\library\FileSystem name($path)
 * @method static  \littler\library\FileSystem basename($path)
 * @method static  \littler\library\FileSystem dirname($path)
 * @method static  \littler\library\FileSystem extension($path)
 * @method static  \littler\library\FileSystem type($path)
 * @method static  \littler\library\FileSystem mimeType($path)
 * @method static  \littler\library\FileSystem size($path)
 * @method static  \littler\library\FileSystem lastModified($path)
 * @method static  \littler\library\FileSystem isDirectory($directory)
 * @method static  \littler\library\FileSystem isReadable($path)
 * @method static  \littler\library\FileSystem isWritable($path)
 * @method static  \littler\library\FileSystem isFile($file)
 * @method static  \littler\library\FileSystem glob($pattern, $flags = 0)
 * @method static  \littler\library\FileSystem files($directory, $hidden = false)
 * @method static  \littler\library\FileSystem allFiles($directory, $hidden = false)
 * @method static  \littler\library\FileSystem directories($directory)
 * @method static  \littler\library\FileSystem makeDirectory($path, $mode = 0755, $recursive = false, $force = false)
 * @method static  \littler\library\FileSystem moveDirectory($from, $to, $overwrite = false)
 * @method static  \littler\library\FileSystem copyDirectory($directory, $destination, $options = null)
 * @method static  \littler\library\FileSystem deleteDirectory($directory, $preserve = false)
 * @method static  \littler\library\FileSystem  deleteDirectories($directory)
 * @method static  \littler\library\FileSystem cleanDirectory($directory)
 */
class FileSystem extends Facade
{
    protected static function getFacadeClass()
    {
        return \littler\library\FileSystem::class;
    }
}
