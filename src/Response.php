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

use think\Paginator;
use think\response\Json;

class Response
{
    /**
     * API 成功的响应.
     *
     * @param array $data
     * @param $msg
     * @param int $code
     */
    public static function api($data = [], $msg = 'success', $code = Code::SUCCESS): Json
    {
        return json([
            'code' => $code,
            'message' => $msg,
            'timestamp' => time(),
            'data' => $data,
        ]);
    }

    /**
     * 成功的响应.
     *
     * @param array $data
     * @param $msg
     * @param int $code
     */
    public static function success($data = [], $msg = 'success', $code = Code::SUCCESS): Json
    {
        return json([
            'code' => $code,
            'message' => $msg,
            'data' => $data,
        ]);
    }

    /**
     * 分页.
     *
     * @return
     */
    public static function paginate(Paginator $list)
    {
        return json([
            'code' => Code::SUCCESS,
            'message' => 'success',
            'count' => $list->total(),
            'current' => $list->currentPage(),
            'limit' => $list->listRows(),
            'data' => $list->getCollection(),
        ]);
    }

    /**
     * 错误的响应.
     *
     * @param string $msg
     * @param int $code
     */
    public static function fail($msg = '', $code = Code::FAILED): Json
    {
        return json([
            'code' => $code,
            'message' => $msg,
        ]);
    }
}
