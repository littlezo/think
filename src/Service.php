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

use littler\event\LoadModuleRoutes;
use littler\middleware\AllowCrossDomain;
use littler\middleware\JsonResponseMiddleware;
use think\exception\Handle;
use think\facade\Validate;

class Service extends \think\Service
{
    public function boot()
    {
        $this->commands([
            Console::class,
        ]);
    }

    /**
     * register.
     */
    public function register()
    {
        $this->registerCommands();
        $this->registerValidates();
        $this->registerMiddleWares();
        $this->registerEvents();
        $this->registerQuery();
        $this->registerProviders();
        $this->registerExceptionHandle();
        $this->registerLoadModule();
        $this->loadModuleRoute();
        $this->registerServices();
        $this->registerEnabledModules();
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $Console = new Console($this->app);

            $this->app->bind('little\console', $Console);

            $this->commands($Console->defaultCommands());
        }
    }

    protected function registerValidates(): void
    {
        $validates = config('little.validates');

        Validate::maker(function ($validate) use ($validates) {
            foreach ($validates as $valid) {
                $valid = app()->make($valid);
                $validate->extend($valid->type(), [$valid, 'verify'], $valid->message());
            }
        });
    }

    protected function registerMiddleWares(): void
    {
        $this->app->middleware->add(AllowCrossDomain::class);
        $this->app->middleware->add(JsonResponseMiddleware::class);
    }

    /**
     * 注册监听者.
     */
    protected function registerEvents(): void
    {
        $this->app->event->listenEvents([
            'RouteLoaded' => [LoadModuleRoutes::class],
        ]);
    }

    /**
     * register query.
     */
    protected function registerQuery(): void
    {
        // sprintf();
        $timeRule = $this->app->config->get('database.time_query_rule');
        $new_time_rule = [
            'hour' => ['1 hour ago', 'now'],
        ];

        $connections = $this->app->config->get('database.connections');

        // 支持多数据库配置注入 Query
        foreach ($connections as &$connection) {
            $connection['query'] = Query::class;
        }

        $this->app->config->set([
            'connections' => $connections,
        ], 'database');
        // 注入时间查询规则
        $this->app->config->set(array_merge($timeRule, $new_time_rule), 'database.time_query_rule');
    }

    /**
     * register Provider.
     */
    protected function registerProviders(): void
    {
        // $this->app->bind('request', Request::class);
        $this->app->bind('littlerApp', App::class);
    }

    /**
     * register exception.
     */
    protected function registerExceptionHandle(): void
    {
        $this->app->bind(Handle::class, ExceptionHandle::class);
    }

    /**
     * 注册模块服务
     */
    protected function registerServices()
    {
        $services = file_exists(App::getCacheServicesFile()) ?
        include App::getCacheServicesFile() :
        App::getEnabledService();
        foreach ($services as $service) {
            if (class_exists($service)) {
                $this->app->register($service);
            }
        }
    }

    /**
     * 注册模块命名空间.
     */
    protected function registerLoadModule()
    {
        $this->app->bind('loadModule', new class() {
            protected $psr4 = [];

            public function loadModule($module)
            {
                [$namespace,$path] = $module;
                $this->psr4 = array_merge($this->psr4, [$namespace => $path]);
                return $this;
            }

            public function get()
            {
                return $this->psr4;
            }
        });
    }

    /**
     * 注册模块路由.
     */
    protected function loadModuleRoute()
    {
        $this->app->instance('moduleRoute', new class() {
            protected $path = [];

            public function loadModuleRoute($path)
            {
                $this->path[] = $path;
                return $this;
            }

            public function get()
            {
                return $this->path;
            }
        });
    }

    /**
     * 注册模块服务
     */
    protected function registerEnabledModules()
    {
        $this->app->instance('enabledModules', new class() {
            public function get()
            {
                return App::getEnabledModules();
            }
        });
        // dd($this->app);
    }
}
