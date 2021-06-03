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
use littler\middleware\DocsMiddleware;
use think\App;

class LoadModuleRoutes
{
    /**
     * 处理.
     */
    public function handle(): void
    {
        $app = app();
        $class = new ParseClass();
        $routes = $route = (array) $class->getRoutes('all');
        $router = $app->get('route');
        $domain = config('little.domain');
        $rest = ['index', 'create', 'edit', 'read', 'save', 'update', 'delete'];
        // dd($routes);
        foreach ($routes as $item) {
            if ($domain) {
                $router->domain($domain, function () use ($router, $item, $rest) {
                    if ($item['resource'] ?? false) {
                        $router->group($item['group'], function () use ($router, $item, $rest) {
                            $router->resource($item['resource'], '\\' . $item['class']);
                            foreach ($item['method'] as $route) {
                                $way = str_replace(['/', '\\'], '', $route['route'][1] ?? '');
                                $pos = strpos($way, ':');
                                $method = $route['route'][0] ?? 'rule';
                                if ($pos) {
                                    $way = substr($way, 0, $pos);
                                }
                                if (! in_array($way, $rest)) {
                                    $router->{$method}($route['resource'] . $route['route'][1] ?? '/error', '\\' . $item['class'] . '@' . $way);
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
                            $router->{$method}($route['route'][1] ?? '/error', '\\' . $item['class'] . '@' . $way);
                        }
                    }
                });
            } else {
                if ($item['resource'] ?? false) {
                    $router->group($item['group'], function () use ($router, $item, $rest) {
                        $router->resource($item['resource'], '\\' . $item['class']);
                        foreach ($item['method'] as $route) {
                            $way = str_replace(['/', '\\'], '', $route['route'][1] ?? '');
                            $pos = strpos($way, ':');
                            $method = $route['route'][0] ?? 'rule';
                            if ($pos) {
                                $way = substr($way, 0, $pos);
                            }
                            if (! in_array($way, $rest)) {
                                $router->{$method}($route['resource'] . $route['route'][1] ?? '/error', '\\' . $item['class'] . '@' . $way);
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
                        $router->{$method}($route['route'][1] ?? '/error', '\\' . $item['class'] . '@' . $way);
                    }
                }
            }
        }
        $router->rule('/docs', '')->middleware(DocsMiddleware::class);
        // dd($router);

        // $paths = app(App::class)->make('moduleRoute')->get();
    }
}
