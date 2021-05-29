<?php

declare(strict_types=1);
/**
 * This file is part of Code Ai.
 *
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @link     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 *
 */
namespace littleZov\event;

use littleZov\Core;
use littleZov\exception\RouteException;
use littleZov\library\ParseClass;
use littleZov\Request;
use think\App;
use think\helper\Str;
use think\Service;

class InitRoute extends Service
{
    /**
     * 应用对象
     *
     * @var \think\App
     */
    protected $app;

    /**
     * 请求对象
     *
     * @var Request
     */
    protected $request;

    /**
     * 模块列表.
     *
     * @var string
     */
    protected $loadModule;

    /**
     * 命名空间.
     *
     * @var string
     */
    protected $namespace;

    /**
     * 模块.
     *
     * @var string
     */
    protected $module;

    /**
     * 控制器名.
     *
     * @var string
     */
    protected $controller;

    /**
     * 操作名.
     *
     * @var string
     */
    protected $action;

    /**
     * 调度信息.
     *
     * @var mixed
     */
    protected $dispatch;

    public function __construct(App $app, Request $request)
    {
        $this->app = $app;
        $this->request = $request;
        $this->loadModule = $app->make('loadModule')->get();
    }

    public function handle()
    {
        $this->initModuleRoute();
        $this->dispatch();
    }

    protected function initModuleRoute(): void
    {
        $parseClass = new ParseClass();
        $domain = config('little.domain');
        $router_list = [];
        $router = $this->app->route;
        $router_list = Core::getRoutes();
        dd($router_list);
        if ($domain) {
            $router->domain($domain, function () use ($router_list, $router) {
                foreach ($router_list as $_scheme => $route) {
                    $class = $route['class'];
                    $methods = $route['methods'];
                    $router->group(function () use ($router, $_scheme, $class, $methods) {
                        $router->resource($_scheme, $class)->mergeRuleRegex();
                        $router->rest([
                            'index' => ['get', '', 'getList'],
                            'read' => ['get', '/:id', 'getInfo'],
                            'create' => ['get', '/create', 'create'],
                            'edit' => ['get', '/edit/:id', 'edit'],
                            'layout' => ['get', '/layout', 'getLayout'],
                            'paginate' => ['get', '/paginate', 'getPage'],
                            'save' => ['post', '', 'save'],
                            'update' => ['put', '/:id', 'update'],
                            'delete' => ['delete', '/:id', 'destroy'],
                        ]);
                        $default_rule = ['index', 'create', 'save', 'read', 'edit', 'update',
                            'delete', 'getList', 'getInfo', 'getLayout', 'getPage', 'destroy',
                        ];
                        foreach ($methods as $item) {
                            if (! in_array($item, $default_rule)) {
                                $router->any($_scheme . '/' . $item, $class . '@' . $item);
                            }
                        }
                    })->mergeRuleRegex();
                }
            })->mergeRuleRegex();
        } else {
            foreach ($router_list as $_scheme => $route) {
                $class = $route['class'];
                $methods = $route['methods'];
                $router->group(function () use ($router, $_scheme, $class, $methods) {
                    $router->resource($_scheme, $class)->mergeRuleRegex();
                    $router->rest([
                        'index' => ['get', '', 'getList'],
                        'read' => ['get', '/:id', 'getInfo'],
                        'create' => ['get', '/create', 'create'],
                        'edit' => ['get', '/edit/:id', 'edit'],
                        'layout' => ['get', '/layout', 'getLayout'],
                        'paginate' => ['get', '/paginate', 'getPage'],
                        'save' => ['post', '', 'save'],
                        'update' => ['put', '/:id', 'update'],
                        'delete' => ['delete', '/:id', 'destroy'],
                    ]);
                    $default_rule = ['index', 'create', 'save', 'read', 'edit', 'update',
                        'delete', 'getList', 'getInfo', 'getLayout', 'getPage', 'destroy',
                    ];
                    foreach ($methods as $item) {
                        if (! in_array($item, $default_rule)) {
                            $router->any($_scheme . '/' . $item, $class . '@' . $item);
                        }
                    }
                })->mergeRuleRegex();
            }
        }
    }

    protected function dispatch($pathinfo = null)
    {
        $parseClass = new ParseClass();
        if (! $pathinfo) {
            $pathinfo = $this->request->pathinfo();
        }
        if (! strpos($pathinfo, '://')) {
            throw new RouteException('目标应用或模块有误！无法调度');
        }
        $pos = strrpos($pathinfo, '://');
        $this->namespace = str_replace(['/', '.', ':'], '', substr($pathinfo, 0, $pos));
        $this->request->apps($this->namespace);
        $this->dispatch = str_replace(['/', '.', '@'], '.', substr($pathinfo, $pos + 3));
        // 获取控制器名
        if (! $this->dispatch || ! is_string($this->dispatch)) {
            throw new RouteException('目标地址有误！无法调度');
        }
        // 解析调度路由
        $param_array = explode('.', $this->dispatch);
        // 获取模块
        $this->module = $param_array[0] ?? 'api';
        $this->request->module($this->module);
        // 获取操作名
        $this->controller = Str::studly($param_array[1] ?? 'index');
        // 获取操作名
        $this->action = $param_array[2] ?? 'index';
        // 设置应用目录和命名空间
        $_namespace = $this->namespace . '\\' . $this->module;
        $this->app->setNamespace($_namespace);
        $_app_path = $this->loadModule[$_namespace] ?? $this->app->getRootPath() . $this->namespace . DIRECTORY_SEPARATOR . $this->module . DIRECTORY_SEPARATOR;
        $this->app->setAppPath($_app_path);
        $parseClass->setPath($_app_path)
            ->setNamespace($this->namespace)
            ->setRule($this->module, $this->controller)
            ->getClass();
        // dd($parseClass);
        // 设置当前请求的控制器、操作
        $this->request
            ->setController($this->controller)
            ->setAction($this->action);
        // dd($this->app->route->getRuleName());
        $this->app->route->any($pathinfo, $this->controller . '/' . Str::camel($this->action));
    }
}
