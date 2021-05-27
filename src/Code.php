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

class Code
{
    public const SUCCESS = 200; // 成功

    public const LOST_LOGIN = 40001; //  登录失效

    public const VALIDATE_FAILED = 40002; // 验证错误

    public const PERMISSION_FORBIDDEN = 40003; // 权限禁止

    public const LOGIN_FAILED = 40004; // 登录失败

    public const FAILED = 40005; // 操作失败

    public const LOGIN_EXPIRED = 40006; // 登录失效

    public const LOGIN_BLACKLIST = 40007; // 黑名单

    public const USER_FORBIDDEN = 40008; // 账户被禁

    public const WECHAT_RESPONSE_ERROR = 40000;
}
