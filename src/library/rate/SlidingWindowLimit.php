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

namespace littler\library\rate;

use littler\exceptions\FailedException;

/**
 * 滑动窗口.
 *
 * Class SlidingWindowLimit
 */
class SlidingWindowLimit
{
	use Redis;

	protected $key;

	protected $limit = 10;

	/**
	 * @var int
	 */
	protected $window = 5;

	public function __construct($key)
	{
		$this->key = $key;
	}

	public function overflow()
	{
		$now = microtime(true) * 1000;

		$redis = $this->getRedis();
		// 开启管道
		$redis->pipeline();
		// 去除非窗口内的元素
		$redis->zremrangeByScore($this->key, 0, $now - $this->window * 1000);
		// 获取集合内的所有元素数目
		$redis->zcard($this->key);
		// 增加元素
		$redis->zadd($this->key, $now, $now);
		// 设置过期
		$redis->expire($this->key, $this->window);
		// 执行管道内命令
		$res = $redis->exec();

		if ($res[1] > $this->limit) {
			throw new FailedException('访问限制');
		}

		return true;
	}

	public function setWindow($time)
	{
		$this->window = $time;

		return $this;
	}
}
