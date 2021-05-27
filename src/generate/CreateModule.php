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
namespace littler\generate;

use littler\App;
use littler\facade\FileSystem;
use littler\library\Composer;

class CreateModule
{
    protected $module;

    protected $moduleDir;

    /**
     * @var string
     */
    protected $stubDir;

    /**
     * @var string
     */
    protected $namespaces;

    protected $name;

    protected $description;

    protected $dirs;

    protected $keywords;

    public function generate($params)
    {
        try {
            $this->module = $params['name'] ?? $params['module'];

            $this->name = $params['title'] ?? $params['module'];

            $this->description = $params['description'] ?? '';

            $this->keywords = $params['keywords'] ?? '';

            $this->dirs = $params['dirs'];

            $this->init();
        } catch (\Exception $exception) {
            $this->rollback();
            dd($exception->getMessage());
        }
    }

    public function init()
    {
        $this->moduleDir = App::moduleDirectory($this->module);

        $this->stubDir = dirname(__DIR__) . DIRECTORY_SEPARATOR .
            'command' . DIRECTORY_SEPARATOR .
            'stubs' . DIRECTORY_SEPARATOR;

        $psr4 = (new Composer())->psr4Autoload();

        foreach ($psr4 as $namespace => $des) {
            if ($des === App::$root) {
                $this->namespaces = $namespace . $this->module . '\\';
                break;
            }
        }

        $this->createFile();
    }

    /**
     * 模块文件.
     *
     * @return string[]
     */
    protected function moduleFiles(): array
    {
        return [
            $this->moduleDir . 'Service.php',
            $this->moduleDir . 'config/info.json',
            $this->moduleDir . 'route.php',
        ];
    }

    /**
     * 创建失败 rollback.
     */
    protected function rollback()
    {
        FileSystem::deleteDirectory($this->moduleDir);
    }

    /**
     * 模块文件夹.
     *
     * @return string[]
     */
    protected function modulePath(): array
    {
        $dirs = [];

        foreach (explode(',', $this->dirs) as $dir) {
            if ($dir == 'database') {
                $dirs[] = $this->moduleDir . 'database' . DIRECTORY_SEPARATOR . 'migrations';
                $dirs[] = $this->moduleDir . 'database' . DIRECTORY_SEPARATOR . 'seeds';
            } else {
                $dirs[] = $this->moduleDir . $dir;
            }
        }
        return $dirs;
    }

    /**
     * 创建路径.
     */
    protected function createDir()
    {
        foreach ($this->modulePath() as $path) {
            App::makeDirectory($path);
        }
    }

    /**
     * 创建文件.
     */
    protected function createFile()
    {
        $this->createDir();
        $this->createService();
        $this->createRoute();
        $this->createModuleJson();
    }

    /**
     * 创建 service.
     */
    protected function createService()
    {
        $service = (string) FileSystem::sharedGet($this->stubDir . 'service.stub');

        $content = str_replace(
            ['{NAMESPACE}', '{SERVICE}'],
            [substr($this->namespaces, 0, -1),
                ucfirst($this->module) . 'Service', ],
            $service
        );

        FileSystem::put($this->moduleDir . ucfirst($this->module) . 'Service.php', $content);
    }

    /**
     * 创建 info.json.
     */
    protected function createModuleJson()
    {
        $infoJson = (string) FileSystem::sharedGet($this->stubDir . 'info.stub');

        $keywords = '';
        foreach (explode(',', $this->keywords) as $k) {
            $keywords .= "\"{$k}\",";
        }

        $content = str_replace(
            ['{NAME}', '{DESCRIPTION}', '{MODULE}', '{KEYWORDS}', '{SERVICE}'],
            [
                $this->name,
                $this->description,
                $this->module,
                trim($keywords, ','),
                '\\\\' . str_replace('\\', '\\\\', $this->namespaces . ucfirst($this->module) . 'Service'),
            ],
            $infoJson
        );

        FileSystem::put($this->moduleDir . 'config/info.json', $content);
    }

    /**
     * 创建路由文件.
     */
    protected function createRoute()
    {
        FileSystem::put($this->moduleDir . 'route.php', FileSystem::sharedGet($this->stubDir . 'route.stub'));
    }
}
