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

class BaseRequest extends Request
{
    public $user;

    public function __construct()
    {
        $this->user = request()->user;
    }

    /**
     * 重写 post.
     *
     * @param string $name
     * @param null $default
     * @param string $filter
     * @return null|array|mixed
     */
    public function post($name = '', $default = null, $filter = '')
    {
        if ($this->needCreatorId) {
        }

        return parent::post($name, $default, $filter);
    }
}
