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

use GuzzleHttp\Promise\Promise;

/**
 * http response.
 *
 * From Laravel
 */
class Response implements \ArrayAccess
{
	/**
	 * @var \GuzzleHttp\Psr7\Response|Promise
	 */
	protected $response;

	public function __construct($response)
	{
		$this->response = $response;
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		// TODO: Implement __call() method.
		return $this->response->{$name}(...$arguments);
	}

	/**
	 * @return bool|callable|float|\GuzzleHttp\Psr7\PumpStream|\GuzzleHttp\Psr7\Stream|int|\Iterator|\Psr\Http\Message\StreamInterface|resource|string|null
	 */
	public function body()
	{
		return $this->response->getBody();
	}

	/**
	 * 响应内容.
	 *
	 * @return false|string
	 */
	public function contents()
	{
		return $this->body()->getContents();
	}

	public function json(): array
	{
		return \json_decode($this->contents(), true);
	}

	public function status(): int
	{
		return $this->response->getStatusCode();
	}

	public function ok(): bool
	{
		return $this->status() == 200;
	}

	public function successful(): bool
	{
		return $this->status() >= 200 && $this->status() < 300;
	}

	public function failed(): bool
	{
		return $this->status() >= 400;
	}

	public function headers(): array
	{
		return $this->response->getHeaders();
	}

	/**
	 * 异步回调.
	 *
	 * @return \GuzzleHttp\Promise\FulfilledPromise|\GuzzleHttp\Promise\PromiseInterface|\GuzzleHttp\Promise\RejectedPromise|Promise
	 */
	public function then(callable $response, callable $exception)
	{
		return $this->response->then($response, $exception);
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		// TODO: Implement offsetExists() method.
		return isset($this->json()[$offset]);
	}

	/**
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		// TODO: Implement offsetGet() method.
		return $this->json()[$offset];
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		// TODO: Implement offsetSet() method.
	}

	public function offsetUnset($offset)
	{
		// TODO: Implement offsetUnset() method.
	}
}
