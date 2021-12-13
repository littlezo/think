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

class RateLimiter
{
	use Redis;

	protected $key;

	/**
	 * 令牌容量.
	 *
	 * @var int
	 */
	protected $capacity = 5;

	/**
	 * 每次添加 token 的数量.
	 *
	 * @var int
	 */
	protected $eachTokens = 5;

	/**
	 * 添加 token 的时间.
	 *
	 * @var string
	 */
	protected $addTokenTimeKey = '_add_token';

	/**
	 * 添加 token 的时间间隔 /s.
	 *
	 * @var int
	 */
	protected $interval = 5;

	/**
	 * RateLimiter constructor.
	 * @param $key
	 */
	public function __construct($key)
	{
		$this->key = $key;
	}

	/**
	 * 处理.
	 */
	public function overflow()
	{
		// 添加 token
		if ($this->canAddToken()) {
			$this->addTokens();
		}

		if (! $this->tokens()) {
			throw new FailedException('访问限制');
		}

		// 每次请求拿走一个 token
		$this->removeToken();
	}

	/**
	 * 设置令牌桶数量.
	 *
	 * @param $capacity
	 * @return $this
	 */
	public function setCapacity($capacity)
	{
		$this->capacity = $capacity;

		return $this;
	}

	/**
	 * 设置时间间隔.
	 *
	 * @param $seconds
	 * @return $this
	 */
	public function setInterval($seconds)
	{
		$this->interval = $seconds;

		return $this;
	}

	/**
	 * d     * @return void
	 */
	protected function addTokens()
	{
		$leftTokens = $this->capacity - $this->tokens();

		$tokens = array_fill(0, $leftTokens < $this->eachTokens ? $leftTokens : $this->eachTokens, 1);

		$this->getRedis()->lPush($this->key, ...$tokens);

		$this->rememberAddTokenTime();
	}

	/**
	 * 拿走一个 token.
	 */
	protected function removeToken()
	{
		$this->getRedis()->rPop($this->key);
	}

	/**
	 * 剩余的 token 数量.
	 *
	 * @return bool|int
	 */
	protected function tokens()
	{
		return $this->getRedis()->lLen($this->key);
	}

	/**
	 * 是否可以添加 token.
	 *
	 * @return bool
	 */
	protected function canAddToken()
	{
		$currentTime = \time();

		$lastAddTokenTime = $this->getRedis()->get($this->key . $this->addTokenTimeKey);

		// 如果是满的 则不添加
		if ($this->tokens() == $this->capacity) {
			return false;
		}

		return ($currentTime - $lastAddTokenTime) > $this->interval;
	}

	/**
	 * 记录添加 token 的时间.
	 */
	protected function rememberAddTokenTime()
	{
		$this->getRedis()->set($this->key . $this->addTokenTimeKey, time());
	}
}
