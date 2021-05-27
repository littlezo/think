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

use littler\exceptions\FailedException;

/**
 * 固定窗口限流
 *
 * Class GrantLimit
 */
class GrantLimit
{
    use Redis;

    protected $ttl = 60;

    protected $limit = 1000;

    protected $key;

    public function __construct($key)
    {
        $this->key = $key;

        $this->init();
    }

    /**
     * 是否到达限流
     */
    public function overflow()
    {
        if ($this->getCurrentVisitTimes() > $this->limit) {
            throw new FailedException('访问限制');
        }

        $this->inc();
    }

    /**
     * 增加接口次数.
     */
    public function inc()
    {
        $this->getRedis()->incr($this->key);
    }

    /**
     * 初始化.
     */
    protected function init()
    {
        if (! $this->getRedis()->exists($this->key)) {
            $this->getRedis()->setex($this->key, $this->ttl, 0);
        }
    }

    /**
     * 获取当前访问次数.
     *
     * @return mixed
     */
    protected function getCurrentVisitTimes()
    {
        return $this->getRedis()->get($this->key);
    }
}
