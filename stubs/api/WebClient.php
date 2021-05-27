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
namespace api;

class WebClient
{
    private $version = '1.0';

    private $request_url;

    private $app_key;

    private $app_secret;

    private $format = 'json';

    private $sign_method = 'md5';

    public static function __construct($app_key, $app_secret)
    {
        if ($app_key == '' || $app_secret == '') {
            throw new \Exception('app_key 和 app_secret 不能为空');
        }

        self::app_key = $app_key;
        self::app_secret = $app_secret;
        self::request_url = 'http://localhost/auth.php';
    }

    public static function get($method, $params = [], $api_version = '1.0')
    {
        return self::parse_response(
            Http::get(self::url($method, $api_version), self::build_request_params($method, $params))
        );
    }

    public static function post($method, $params = [], $files = [], $api_version = '1.0')
    {
        self::version = $api_version;
        return self::parse_response(
            Http::post(self::url($method, $api_version), self::build_request_params($method, $params), $files)
        );
    }

    public static function url($method, $api_version = '1.0')
    {
        return self::request_url;
    }

    public static function set_format($format)
    {
        if (! in_array($format, ApiProtocol::allowed_format())) {
            throw new \Exception('设置的数据格式错误');
        }

        self::format = $format;

        return self;
    }

    public static function set_sign_method($method)
    {
        if (! in_array($method, ApiProtocol::allowed_sign_methods())) {
            throw new \Exception('设置的签名方法错误');
        }

        self::sign_method = $method;

        return self;
    }

    private function parse_response($response_data)
    {
        return json_decode($response_data, true);
    }

    private function build_request_params($method, $api_params)
    {
        if (! is_array($api_params)) {
            $api_params = [];
        }
        $pairs = self::get_common_params($method);
        foreach ($api_params as $k => $v) {
            if (isset($pairs[$k])) {
                throw new \Exception('参数名冲突');
            }
            $pairs[$k] = $v;
        }
        $pairs[ApiProtocol::SIGN_KEY] = ApiProtocol::sign(self::app_secret, $pairs, self::sign_method);
        return $pairs;
    }

    private function get_common_params($method)
    {
        $params = [];
        $params[ApiProtocol::APP_ID_KEY] = self::app_key;
        $params[ApiProtocol::METHOD_KEY] = $method;
        $params[ApiProtocol::TIMESTAMP_KEY] = date('Y-m-d H:i:s');
        $params[ApiProtocol::FORMAT_KEY] = self::format;
        $params[ApiProtocol::SIGN_METHOD_KEY] = self::sign_method;
        $params[ApiProtocol::VERSION_KEY] = self::version;
        return $params;
    }
}
