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
use littler\facade\FileSystem;
use littler\library\Composer;

class Module
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

    public function done($params)
    {
        // dd($params);
        try {
            $this->module = $params['module'];

            $this->name = $params['title'] ?? $params['module'];

            $this->description = $params['description'] ?? '';

            $this->keywords = $params['keywords'] ?? '';
            $this->init();
        } catch (\Exception $exception) {
            $this->rollback();
            return $exception->getTraceAsString();
        }
    }

    public function init()
    {
        $this->moduleDir = App::moduleDirectory($this->module);

        $this->stubDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR .
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
     * 创建失败 rollback.
     */
    protected function rollback()
    {
        FileSystem::deleteDirectory($this->moduleDir);
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
            $this->moduleDir . 'config/config.json',
            $this->moduleDir . 'config/event.json',
            // $this->moduleDir . 'route.php',
        ];
    }

    /**
     * 模块文件夹.
     *
     * @return string[]
     */
    protected function modulePath(): array
    {
        $dirs = [];
        $dirs[] = $this->moduleDir . 'config';
        $dirs[] = $this->moduleDir . 'event';
        $dirs[] = $this->moduleDir . 'model';
        $dirs[] = $this->moduleDir . 'repository';
        $dirs[] = $this->moduleDir . 'service';
        $dirs[] = $this->moduleDir . 'admin' . DIRECTORY_SEPARATOR . 'controller';
        $dirs[] = $this->moduleDir . 'api' . DIRECTORY_SEPARATOR . 'controller';
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
        // $this->createRoute();
        $this->createConfig();
        $this->createEvent();
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
                'Service', ],
            $service
        );
        // dd($content);
        FileSystem::put($this->moduleDir . 'Service.php', $content);
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
            ['{NAME}', '{DESCRIPTION}', '{MODULE}', '{KEYWORDS}', '{SERVICE}', '{VERSION_NO}'],
            [
                $this->name,
                $this->description,
                $this->module,
                trim($keywords, ','),
                '\\\\' . str_replace('\\', '\\\\', $this->namespaces . 'Service'),
                date('Ymd', time()),
            ],
            $infoJson
        );

        FileSystem::put($this->moduleDir . 'config/info.json', $content);
    }

    /**
     * 创建事件.
     */
    protected function createConfig()
    {
        FileSystem::put($this->moduleDir . 'config/config.php', FileSystem::sharedGet($this->stubDir . 'config.stub'));
    }

    /**
     * 创建事件.
     */
    protected function createEvent()
    {
        FileSystem::put($this->moduleDir . 'config/event.php', FileSystem::sharedGet($this->stubDir . 'event_config.stub'));
    }

    /**
     * 创建路由文件.
     */
    protected function createRoute()
    {
        FileSystem::put($this->moduleDir . 'route.php', FileSystem::sharedGet($this->stubDir . 'route.stub'));
    }
}
