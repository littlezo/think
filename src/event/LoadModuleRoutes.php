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
namespace littler\event;

use littler\library\ParseClass;
use think\App;
use think\Route;

class LoadModuleRoutes
{
    /**
     * 处理.
     */
    public function handle(): void
    {
        $app = app();
        $routes = [];

        $class = new ParseClass();
        foreach ($app->loadModule->get() as $namespace => $path) {
            [$namespace, $module] = explode('\\', $namespace);
            $route = (array) $class->setNamespace($namespace)->setPath($path)->setModule($module)->getRoutes('admin\controller');
            $routes = array_merge($routes, $route);
        }
        // dd($routes);

        $router = app(Route::class);
        $domain = config('little.domain');
        $rest = ['index', 'create', 'edit', 'read', 'save', 'update', 'delete'];
        foreach ($routes as $item) {
            if ($domain) {
                $router->domain($domain, function () use ($router, $item, $rest) {
                    if ($item['resource'] ?? false) {
                        $router->group($item['group'], function () use ($router, $item, $rest) {
                            $router->resource($item['resource'], '\\' . $item['namespace'] . '\\' . $item['class']);
                            foreach ($item['method'] as $route) {
                                $way = str_replace(['/', '\\'], '', $route['route'][1] ?? '');
                                $pos = strpos($way, ':');
                                $method = $route['route'][0] ?? 'rule';
                                if ($pos) {
                                    $way = substr($way, 0, $pos);
                                }
                                if (! in_array($way, $rest)) {
                                    $router->{$method}($route['resource'] . $route['route'][1] ?? '/error', '\\' . $item['namespace'] . '\\' . $item['class'] . '@' . $way);
                                }
                            }
                        })->mergeRuleRegex();
                    } else {
                        foreach ($item['method'] as $route) {
                            $way = str_replace(['/', '\\'], '', $route['route'][1] ?? '');
                            $pos = strpos($way, ':');
                            $method = $route['route'][0] ?? 'rule';
                            if ($pos) {
                                $way = substr($way, 0, $pos);
                            }
                            $router->{$method}($route['route'][1] ?? '/error', '\\' . $item['namespace'] . '\\' . $item['class'] . '@' . $way);
                        }
                    }
                });
            } else {
                if ($item['resource'] ?? false) {
                    $router->group($item['group'], function () use ($router, $item, $rest) {
                        $router->resource($item['resource'], '\\' . $item['namespace'] . '\\' . $item['class']);
                        foreach ($item['method'] as $route) {
                            $way = str_replace(['/', '\\'], '', $route['route'][1] ?? '');
                            $pos = strpos($way, ':');
                            $method = $route['route'][0] ?? 'rule';
                            if ($pos) {
                                $way = substr($way, 0, $pos);
                            }
                            if (! in_array($way, $rest)) {
                                $router->{$method}($route['resource'] . $route['route'][1] ?? '/error', '\\' . $item['namespace'] . '\\' . $item['class'] . '@' . $way);
                            }
                        }
                    })->mergeRuleRegex();
                } else {
                    foreach ($item['method'] as $route) {
                        $way = str_replace(['/', '\\'], '', $route['route'][1] ?? '');
                        $pos = strpos($way, ':');
                        $method = $route['route'][0] ?? 'rule';
                        if ($pos) {
                            $way = substr($way, 0, $pos);
                        }
                        $router->{$method}($route['route'][1] ?? '/error', '\\' . $item['namespace'] . '\\' . $item['class'] . '@' . $way);
                    }
                }
            }
        }

        // dd($router);

        // $paths = app(App::class)->make('moduleRoute')->get();
    }
}
