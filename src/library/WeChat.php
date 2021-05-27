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
namespace littler\library;

use EasyWeChat\Factory;
use littler\exceptions\WechatResponseException;
use think\helper\Str;

/**
 * @method static officialAccount()
 * @method static miniProgram()
 * @method static openPlatform()
 * @method static work()
 * @method static openWork()
 * @method static payment()
 *
 * Class WeChat
 */
class WeChat
{
    /**
     * 静态调用.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {// TODO: Implement __callStatic() method.
        return Factory::{$name}(\config('wechat.' . Str::snake($name)));
    }

    /**
     * 动态调用.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        return Factory::{$name}(\config('wechat.' . Str::snake($name)));
    }

    /**
     * throw error.
     *
     * @param $response
     * @return bool
     */
    public static function throw($response)
    {
        if (isset($response['errcode']) && $response['errcode']) {
            $message = Errors::WECHAT[$response['errcode']] ?? $response['errcode'];
            throw new WechatResponseException($message, $response['errcode']);
        }

        return $response;
    }
}
