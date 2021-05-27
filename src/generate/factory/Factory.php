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
namespace littler\generate\factory;

use littler\App;
use think\facade\Db;

abstract class Factory
{
    abstract public function done(array $params);

    /**
     * parse psr4 path.
     *
     * @return mixed
     */
    public function parsePsr4()
    {
        $composer = \json_decode(file_get_contents(root_path() . 'composer.json'), true);

        return $composer['autoload']['psr-4'];
    }

    /**
     * 获取模块地址
     *
     * @param $filePath
     */
    public function getModulePath($filePath): string
    {
        $path = explode('\\', $filePath);

        $projectRootNamespace = array_shift($path);

        $module = array_shift($path);

        $psr4 = $this->parsePsr4();

        return root_path() . $psr4[$projectRootNamespace . '\\'] . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;
    }

    /**
     * parse filename.
     *
     * @param $filename
     */
    public function parseFilename($filename): array
    {
        $namespace = explode('\\', $filename);

        $className = ucfirst(array_pop($namespace));

        $namespace = implode('\\', $namespace);

        return [$className, $namespace];
    }

    /**
     * @param $table
     */
    public function hasTableExists($table)
    {
        $tables = Db::connect()->getTables();
        // dd(in_array($table, $tables) ? $table : false);
        return in_array($table, $tables) ? $table : false;
    }

    /**
     * get generate path.
     *
     * @param $filePath
     */
    protected function getGeneratePath($filePath): string
    {
        $path = explode('\\', $filePath);

        $projectRootNamespace = array_shift($path);

        $filename = array_pop($path);

        $psr4 = $this->parsePsr4();

        $filePath = root_path() . $psr4[$projectRootNamespace . '\\'] . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $path);

        App::makeDirectory($filePath);

        return $filePath . DIRECTORY_SEPARATOR . ucfirst($filename) . '.php';
    }
}
