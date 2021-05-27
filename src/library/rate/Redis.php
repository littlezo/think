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
namespace littler\library\rate;

use think\facade\Cache;

trait Redis
{
    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * 设置 ttl.
     *
     * @param $ttl
     * @return $this
     */
    public function ttl($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * 设置限制次数.
     *
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * 返回 redis.
     */
    protected function getRedis(): \Redis
    {
        if (! $this->redis) {
            $this->redis = Cache::store('redis')->handler();
        }

        return $this->redis;
    }
}
