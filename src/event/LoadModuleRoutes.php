<?php

declare(strict_types=1);

/*
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。
 * ## 只要思想不滑稽，方法总比苦难多！
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

class LoadModuleRoutes
{
	/**
	 * 处理.
	 */
	public function handle(): void
	{
		// $app = app();
		// $class = new ParseClass();
		// $routes = (array) $class->getRoutes('all');
		// $router = $app->get('route');
		// $domain = config('little.domain');
		// $rest = ['index', 'create', 'edit', 'read', 'save', 'update', 'delete'];
		// foreach ($routes as $item) {
		// 	if ($domain) {
		// 		$router->domain($domain, function () use ($router, $item, $rest) {
		// 			$router->group($item['group'], function () use ($router, $item, $rest) {
		// 				if ($item['auth']) {
		// 					$router->resource($item['resource'], '\\' . $item['class'])->middleware($item['auth'])->except(['create', 'edit']);
		// 				} else {
		// 					$router->resource($item['resource'], '\\' . $item['class'])->except(['create', 'edit']);
		// 				}
		// 				foreach ($item['method'] as $route) {
		// 					$way = str_replace(['/', '\\'], '', $route['route'][1] ?? '');
		// 					$pos = strpos($way, ':');
		// 					$method = $route['route'][0] ?? 'rule';
		// 					$param = '';
		// 					if ($pos) {
		// 						$param = '/<' . substr($way, $pos + 1) . '>';
		// 						$way = substr($way, 0, $pos);
		// 					}
		// 					if (! in_array($way, $rest, true)) {
		// 						if (! $route['is_allow'] && $item['auth']) {
		// 							$router->{$method}($route['resource'] . $route['route'][1] ?? '' . $param, '\\' . $item['class'] . '@' . $way)->middleware($item['auth']);
		// 						} else {
		// 							$router->{$method}($route['resource'] . $route['route'][1] ?? '' . $param, '\\' . $item['class'] . '@' . $way);
		// 						}
		// 					}
		// 				}
		// 			})->mergeRuleRegex();
		// 		});
		// 	} else {
		// 		$router->group($item['group'], function () use ($router, $item, $rest) {
		// 			if ($item['auth']) {
		// 				$router->resource($item['resource'], '\\' . $item['class'])->middleware($item['auth'])->except(['create', 'edit']);
		// 			} else {
		// 				$router->resource($item['resource'], '\\' . $item['class'])->except(['create', 'edit']);
		// 			}
		// 			foreach ($item['method'] as $route) {
		// 				$way = str_replace(['/', '\\'], '', $route['route'][1] ?? '');
		// 				$pos = strpos($way, ':');
		// 				$method = $route['route'][0] ?? 'rule';
		// 				$param = '';
		// 				if ($pos) {
		// 					$param = '/<' . substr($way, $pos + 1) . '>';
		// 					$way = substr($way, 0, $pos);
		// 				}
		// 				if (! in_array($way, $rest, true)) {
		// 					if (! $route['is_allow'] && $item['auth']) {
		// 						$router->{$method}($route['resource'] . $route['route'][1] ?? '' . $param, '\\' . $item['class'] . '@' . $way)->middleware($item['auth']);
		// 					} else {
		// 						$router->{$method}($route['resource'] . $route['route'][1] ?? '' . $param, '\\' . $item['class'] . '@' . $way);
		// 					}
		// 				}
		// 			}
		// 		})->mergeRuleRegex();
		// 	}
		// }
	}
}
