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
namespace littler;

use littler\facade\FileSystem;
use littler\library\Composer;
use think\App;

class Console
{
    protected $app;

    protected $namespace = '';

    protected $path = __DIR__ . DIRECTORY_SEPARATOR . 'command';

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 获取 commands.
     */
    public function commands(): array
    {
        $commandFiles = FileSystem::allFiles($this->path);

        $commands = [];

        /* \Symfony\Component\Finder\SplFileInfo $command */
        foreach ($commandFiles as $command) {
            if ($command->getExtension() === 'php') {
                $lastPath = str_replace($this->parseNamespace(), '', pathinfo($command->getPathname(), PATHINFO_DIRNAME));
                $namespace = $this->namespace . str_replace(DIRECTORY_SEPARATOR, '\\', $lastPath) . '\\';
                $commandClass = $namespace . pathinfo($command->getPathname(), PATHINFO_FILENAME);
                $commands[] = $commandClass;
            }
        }

        return $commands;
    }

    /**
     * set path.
     *
     * @param $path
     * @return $this
     */
    public function path($path): Console
    {
        $this->path = $path;

        return $this;
    }

    /**
     * 设置命名空间.
     *
     * @param $namespace
     * @return $this
     */
    public function setNamespace($namespace): Console
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * 默认 commands.
     */
    public function defaultCommands(): array
    {
        $defaultCommands = FileSystem::allFiles(__DIR__ . DIRECTORY_SEPARATOR . 'command');

        $commands = [];

        /* \Symfony\Component\Finder\SplFileInfo $command */
        foreach ($defaultCommands as $command) {
            if ($command->getExtension() === 'php') {
                $filename = str_replace('.php', '', str_replace(__DIR__, '', $command->getPathname()));

                $class = 'littler' . str_replace(DIRECTORY_SEPARATOR, '\\', $filename);

                if (class_exists($class)) {
                    $commands[] = $class;
                }
            }
        }

        return $commands;
    }

    /**
     * 命名空间解析.
     */
    protected function parseNamespace(): string
    {
        $psr4 = (new Composer())->psr4Autoload();

        if (strpos($this->namespace, '\\') === false) {
            $rootNamespace = $this->namespace . '\\';
        } else {
            $rootNamespace = substr($this->namespace, 0, strpos($this->namespace, '\\') + 1);
        }

        $path = root_path() . $psr4[$rootNamespace] . DIRECTORY_SEPARATOR;

        if (strpos($this->namespace, '\\') !== false) {
            $path .= str_replace('\\', DIRECTORY_SEPARATOR, substr($this->namespace, strpos($this->namespace, '\\') + 1));
        }

        return rtrim($path, '/');
    }
}
