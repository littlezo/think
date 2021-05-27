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

use think\App;
use think\Route;

class LoadModuleRoutes
{
    /**
     * 处理.
     */
    public function handle(): void
    {
        $router = app(Route::class);
        $domain = config('little.domain');
        $paths = app(App::class)->make('moduleRoute')->get();
        if ($domain) {
            $router->domain($domain, function () use ($router, $paths) {
                foreach ($paths as $path) {
                    include $path;
                }
            });
        } else {
            $router->group(function () use ($router, $paths) {
                foreach ($paths as $path) {
                    include $path;
                }
            });
        }
    }
}
