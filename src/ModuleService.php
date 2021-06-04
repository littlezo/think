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

use think\Service;

abstract class ModuleService extends Service
{
    abstract public function loadModuleRoute();

    abstract public function loadModule();

    abstract public function loadMiddleware();

    abstract public function loadEvents();

    abstract public function loadConfig();

    abstract public function loadCommands();

    /**
     * 注册.
     */
    public function register()
    {
        $this->registerModule();
        $this->registerModuleRoute();
        $this->registerEvents();
        $this->registerMiddleWares();
        $this->registerConfig();
        $this->registerCommands();
    }

    /**
     * 模块注册.
     */
    protected function registerModule()
    {
        $model[] = $this->loadModule();
        if (method_exists($this, 'loadModule') && ! is_null($this->loadModule())) {
            $this->app->make('loadModule')->loadModule($this->loadModule());
        }
    }

    /**
     * 路由注册.
     */
    protected function registerModuleRoute()
    {
        $model[] = $this->loadModuleRoute();
        if (method_exists($this, 'loadModuleRoute') && ! is_null($this->loadModuleRoute())) {
            // dd($this->loadModuleRoute());
            $this->app->make('moduleRoute')->loadModuleRoute($this->loadModuleRoute());
        }
    }

    /**
     * 事件注册.
     */
    protected function registerEvents()
    {
        if (method_exists($this, 'loadEvents') && ! is_null($this->loadEvents())) {
            // echo json_encode($this->loadEvents());
            $listen = $this->loadEvents()['listen'] ?? [];
            if (! empty($listen)) {
                $this->app->event->listenEvents($listen);
            }
        }
    }

    protected function registerMiddleWares()
    {
        if (method_exists($this, 'loadMiddleware') && ! is_null($this->loadMiddleware())) {
            $this->app->config->set(array_merge($this->app->config->get('middleware'), $this->loadMiddleware()), 'middleware');
        }
    }

    /**
     * 配置注册.
     */
    protected function registerConfig()
    {
        if (method_exists($this, 'loadConfig') && ! is_null($this->loadConfig())) {
            foreach ($this->app->config->get() as $config_key => $config_value) {
                foreach ($this->loadConfig() as $key => $value) {
                    if ($key == $config_key) {
                        $this->app->config->set(array_merge($config_value, $value), $config_key);
                    } else {
                        $this->app->config->set($value, $key);
                    }
                }
            }
        }
    }

    /**
     * 命令注册.
     */
    protected function registerCommands()
    {
        if (method_exists($this, 'loadCommands') && ! is_null($this->loadCommands()) && $this->app->runningInConsole()) {
            [$namespace, $path] = $this->loadCommands();
            // dd($path);
            if ($this->app->has('little\console')) {
                $littleConsole = $this->app['little\console'];
                $this->commands($littleConsole->setNamespace($namespace)
                    ->path($path)
                    ->commands());
            }
        }
    }
}
