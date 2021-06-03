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
namespace littler\exceptions;

use Exception;
use think\exception\HttpException;

abstract class ApiException extends HttpException
{
    protected const HTTP_SUCCESS = 500;

    public function __construct(string $message = '', int $code = 0, Exception $previous = null, array $headers = [], $statusCode = 0)
    {
        parent::__construct($statusCode, $message ?: $this->getMessage(), $previous, $headers, $code);
    }

    public function getStatusCode()
    {
        return self::HTTP_SUCCESS;
    }
}
