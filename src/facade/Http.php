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

namespace littler\facade;

use think\Facade;

/**
 * @method static \littler\library\client\Http headers(array $headers)
 * @method static \littler\library\client\Http body($body)
 * @method static \littler\library\client\Http json(array $data)
 * @method static \littler\library\client\Http query(array $query)
 * @method static \littler\library\client\Http form(array $params)
 * @method static \littler\library\client\Http timeout($timeout)
 * @method static \littler\library\client\Http get(string $url)
 * @method static \littler\library\client\Http post(string $url)
 * @method static \littler\library\client\Http put(string $url)
 * @method static \littler\library\client\Http delete(string $url)
 * @method static \littler\library\client\Http token(string $token)
 * @method static \littler\library\client\Http ignoreSsl()
 * @method static \littler\library\client\Http attach($name, $resource, $filename)
 */
class Http extends Facade
{
	protected static $alwaysNewInstance = true;

	protected static function getFacadeClass()
	{
		return \littler\library\client\Http::class;
	}
}
