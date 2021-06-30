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

/**
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。.
 *
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @see     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 */
class ApiProtocol
{
	public const APP_ID_KEY = 'app_key';

	public const METHOD_KEY = 'method';

	public const TIMESTAMP_KEY = 'timestamp';

	public const FORMAT_KEY = 'format';

	public const VERSION_KEY = 'version';

	public const SIGN_KEY = 'sign';

	public const SIGN_METHOD_KEY = 'sign_method';

	public const TOKEN_KEY = 'access_token';

	public const ALLOWED_DEVIATE_SECONDS = 600;

	public const ERR_SYSTEM = -1;

	public const ERR_INVALID_APP_ID = 40001;

	public const ERR_INVALID_APP = 40002;

	public const ERR_INVALID_TIMESTAMP = 40003;

	public const ERR_EMPTY_SIGNATURE = 40004;

	public const ERR_INVALID_SIGNATURE = 40005;

	public const ERR_INVALID_METHOD_NAME = 40006;

	public const ERR_INVALID_METHOD = 40007;

	public const ERR_INVALID_TEAM = 40008;

	public const ERR_PARAMETER = 41000;

	public const ERR_LOGIC = 50000;

	public static function sign($appSecret, $params, $method = 'md5')
	{
		if (! is_array($params)) {
			$params = [];
		}

		ksort($params);
		$text = '';
		foreach ($params as $k => $v) {
			$text .= $k . $v;
		}

		return self::hash($method, $appSecret . $text . $appSecret);
	}

	public static function allowed_sign_methods()
	{
		return ['md5'];
	}

	public static function allowed_format()
	{
		return ['json'];
	}

	private static function hash($method, $text)
	{
		switch ($method) {
			case 'md5':
			default:
				$signature = md5($text);
				break;
		}
		return $signature;
	}
}
