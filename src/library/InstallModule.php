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

use littler\App;
use littler\command\MigrateCreateCommand;
use littler\command\MigrateRollbackCommand;
use littler\command\SeedRunCommand;
use littler\facade\FileSystem;
use think\facade\Console;

class InstallModule
{
    protected $installPath;

    protected $module;

    protected $compress;

    public function __construct()
    {
        $this->compress = new Compress();

        $this->registerCommands();
    }

    public function run()
    {
        if ($this->isFirstInstall()) {
            if ($this->download()) {
                $this->install();
            }
        }
    }

    /**
     * 是否是首页安装.
     *
     * @return bool
     */
    public function isFirstInstall()
    {
        return ! FileSystem::exists($this->getInstallPath() . $this->module);
    }

    /**
     * 搜索模块.
     *
     * @return string
     */
    public function searchModule()
    {
        return 'http://api.stye.cn/hello.zip';
    }

    /**
     * 下载.
     *
     * @return string
     */
    public function download()
    {
        return $this->compress->savePath($this->getModuleZip())->download($this->searchModule());
    }

    /**
     * 安装.
     *
     * @throws \Exception
     */
    public function install()
    {
        $this->extractTo();

        $this->installDatabaseTables();

        $this->installTableData();
    }

    /**
     * 解压.
     *
     * @throws \Exception
     * @return bool
     */
    public function extractTo()
    {
        $zip = new Zip();

        $zip->make($this->getModuleZip())->extractTo($this->getInstallPath())->close();

        return true;
    }

    /**
     * 安装表.
     */
    public function installDatabaseTables()
    {
        Console::call('lz-migrate:run', [$this->module]);
    }

    /**
     * 初始化表数据.
     */
    public function installTableData()
    {
        Console::call('lz-seed:run', [$this->module]);
    }

    /**
     * 设置模块.
     *
     * @param $module
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * 设置安装目录.
     *
     * @param $path
     * @return $this
     */
    public function setInstallPath($path)
    {
        $this->installPath = $path;

        return $this;
    }

    /**
     * 获取模块.
     *
     * @return string
     */
    protected function getModuleZip()
    {
        return $this->downloadPath() . $this->module . '_' . date('YmdHis') . '.zip';
    }

    /**
     * 注册命令.
     */
    protected function registerCommands()
    {
        return app()->console->addCommands([
            MigrateCreateCommand::class,
            SeedRunCommand::class,
            MigrateRollbackCommand::class,
        ]);
    }

    /**
     * 下载路径.
     *
     * @return string
     */
    protected function downloadPath()
    {
        $path = runtime_path('little' . DIRECTORY_SEPARATOR . 'download');

        if (! FileSystem::exists($path)) {
            FileSystem::makeDirectory($path, 0777, true);
        }

        return $path;
    }

    /**
     * 获取安装路径.
     *
     * @return string
     */
    protected function getInstallPath()
    {
        if ($this->installPath) {
            return root_path($this->installPath);
        }

        return App::directory();
    }
}
