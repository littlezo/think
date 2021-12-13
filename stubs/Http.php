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

use GuzzleHttp\Client;

class Http
{
	/**
	 * @var Client
	 */
	protected $client;

	/**
	 * auth.
	 *
	 * @var array
	 */
	protected $auth = [];

	/**
	 * 代理.
	 *
	 * @var array
	 */
	protected $proxy = [];

	/**
	 * body.
	 *
	 * @var array
	 */
	protected $body = [];

	/**
	 * header.
	 *
	 * @var array
	 */
	protected $header = [];

	/**
	 * form params.
	 *
	 * @var array
	 */
	protected $formParams = [];

	/**
	 * query set.
	 *
	 * @var array
	 */
	protected $query = [];

	/**
	 * json set.
	 *
	 * @var array
	 */
	protected $json = [];

	/**
	 *  可选参数.
	 * @var array
	 */
	protected $options = [];

	/**
	 * 异步请求
	 *
	 * @var bool
	 */
	protected $async = false;

	/**
	 * @var array
	 */
	protected $timeout = [];

	/**
	 * @var string
	 */
	protected $token = '';

	protected $multipart = [];

	/**
	 * 忽略证书.
	 *
	 * @var array
	 */
	protected $ignoreSsl = [];

	/**
	 * 获取 Guzzle 客户端.
	 *
	 * @return Client
	 */
	public function getClient()
	{
		if (! $this->client) {
			$this->client = new Client();
		}

		return $this->client;
	}

	/**
	 * headers.
	 *
	 * @return $this
	 */
	public function headers(array $headers)
	{
		$this->header = isset($this->header['headers']) ?
						array_merge($this->header['headers'], $headers) :
						['headers' => $headers];

		return $this;
	}

	/**
	 * set bearer token.
	 *
	 * @return $this
	 */
	public function token(string $token)
	{
		$this->header['headers']['authorization'] = 'Bearer ' . $token;

		return $this;
	}

	/**
	 * body.
	 *
	 * @param $body
	 * @return $this
	 */
	public function body($body)
	{
		$this->body = [
			'body' => $body,
		];

		return $this;
	}

	/**
	 * json.
	 *
	 * @return $this
	 */
	public function json(array $data)
	{
		$this->json = [
			'json' => $data,
		];

		return $this;
	}

	/**
	 * query.
	 *
	 * @return $this
	 */
	public function query(array $query)
	{
		$this->query = [
			'query' => $query,
		];

		return $this;
	}

	/**
	 * form params.
	 *
	 * @param $params
	 * @return $this
	 */
	public function form(array $params)
	{
		$this->formParams = [
			'form_params' => array_merge($this->multipart, $params),
		];

		return $this;
	}

	/**
	 * timeout.
	 *
	 * @param $timeout
	 * @return $this
	 */
	public function timeout($timeout)
	{
		$this->timeout = [
			'connect_timeout' => $timeout,
		];

		return $this;
	}

	/**
	 * 忽略 ssl 证书.
	 *
	 * @return $this
	 */
	public function ignoreSsl()
	{
		$this->ignoreSsl = [
			'verify' => false,
		];

		return $this;
	}

	/**
	 * 可选参数.
	 *
	 * @return $this
	 */
	public function options(array $options)
	{
		$this->options = $options;

		return $this;
	}

	/**
	 * Request get.
	 *
	 * @return Response
	 */
	public function get(string $url)
	{
		return new Response($this->getClient()->{$this->asyncMethod(__FUNCTION__)}($url, $this->merge()));
	}

	/**
	 * Request post.
	 *
	 * @param $url
	 * @return mixed
	 */
	public function post(string $url)
	{
		return new Response($this->getClient()->{$this->asyncMethod(__FUNCTION__)}($url, $this->merge()));
	}

	/**
	 * Request put.
	 *
	 * @param $url
	 * @return mixed
	 */
	public function put(string $url)
	{
		return new Response($this->getClient()->{$this->asyncMethod(__FUNCTION__)}($url, $this->merge()));
	}

	/**
	 * Request delete.
	 *
	 * @param $url
	 * @return mixed
	 */
	public function delete(string $url)
	{
		return new Response($this->getClient()->{$this->asyncMethod(__FUNCTION__)}($url, $this->merge()));
	}

	/**
	 * 异步请求
	 *
	 * @return $this
	 */
	public function async()
	{
		$this->async = true;

		return $this;
	}

	/**
	 * 附件.
	 *
	 * @param $name
	 * @param $resource
	 * @param $filename
	 * @return $this
	 */
	public function attach(string $name, $resource, string $filename)
	{
		$this->multipart = [
			'multipart' => [
				[
					'name' => $name,
					'contents' => $resource,
					'filename' => $filename,
				],
			],
		];

		return $this;
	}

	/**
	 * onHeaders.
	 *
	 * @return mixed
	 */
	public function onHeaders(callable $callable)
	{
		$this->options['on_headers'] = $callable;

		return $this;
	}

	/**
	 * onStats.
	 *
	 * @return mixed
	 */
	public function onStats(callable $callable)
	{
		$this->options['on_stats'] = $callable;

		return $this;
	}

	/**
	 * 认证
	 *
	 * @param $username
	 * @param $password
	 * @return $this
	 */
	public function auth($username, $password)
	{
		$this->options = [
			'auth' => $username, $password,
		];

		return $this;
	}

	/**
	 * proxy.
	 *
	 * @return $this
	 */
	public function proxy(array $proxy)
	{
		$this->proxy = $proxy;

		return $this;
	}

	/**
	 * request params merge.
	 *
	 * @return array
	 */
	protected function merge()
	{
		return array_merge(
			$this->header,
			$this->query,
			$this->timeout,
			$this->options,
			$this->body,
			$this->auth,
			$this->multipart,
			$this->formParams,
			$this->ignoreSsl
		);
	}

	/**
	 * 异步方法.
	 *
	 * @param $method
	 * @return string
	 */
	protected function asyncMethod($method)
	{
		return $this->async ? $method . 'Async' : $method;
	}
}
