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

/**
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。.
 *
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @see     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 */
class Sign
{
	private $version = '1.0';

	private $request_url;

	private $app_key;

	private $app_secret;

	private $format = 'json';

	private $sign_method = 'md5';

	public function __construct($app_key, $app_secret)
	{
		if ($app_key == '' || $app_secret == '') {
			throw new \Exception('app_key 和 app_secret 不能为空');
		}

		$this->app_key = $app_key;
		$this->app_secret = $app_secret;
		$this->request_url = DOMAIN . '/api';
	}

	public function get($method, $api_version, $params = [])
	{
		return $this->parse_response(
			Http::get($this->url($method, $api_version), $this->build_request_params($method, $params))
		);
	}

	public function post($method, $params = [], $files = [], $api_version = '1.0')
	{
		$this->version = $api_version;
		return $this->parse_response(
			Http::post($this->url($method, $api_version), $this->build_request_params($method, $params), $files)
		);
	}

	public function url($method, $api_version = '1.0')
	{
		$method = 'index/index/method/' . $method . '/version/' . $api_version;
		return $this->request_url . $method;
	}

	public function set_format($format)
	{
		if (! in_array($format, ApiProtocol::allowed_format(), true)) {
			throw new \Exception('设置的数据格式错误');
		}

		$this->format = $format;

		return $this;
	}

	public function set_sign_method($method)
	{
		if (! in_array($method, ApiProtocol::allowed_sign_methods(), true)) {
			throw new \Exception('设置的签名方法错误');
		}

		$this->sign_method = $method;

		return $this;
	}

	private function parse_response($response_data)
	{
		$data = json_decode($response_data, true);
		if ($data === null) {
			throw new \Exception('response invalid, data: ' . $response_data);
		}
		return $data;
	}

	private function build_request_params($method, $api_params)
	{
		if (! is_array($api_params)) {
			$api_params = [];
		}
		if ($this->app_key) {
		}
		$pairs = $this->get_common_params($method);
		foreach ($api_params as $k => $v) {
			if (isset($pairs[$k])) {
				throw new \Exception('参数名冲突');
			}
			$pairs[$k] = $v;
		}
		$pairs[ApiProtocol::SIGN_KEY] = ApiProtocol::sign($this->app_secret, $pairs, $this->sign_method);
		return $pairs;
	}

	private function get_common_params($method)
	{
		$params = [];
		$params[ApiProtocol::APP_ID_KEY] = $this->app_key;
		$params[ApiProtocol::METHOD_KEY] = $method;
		$params[ApiProtocol::TIMESTAMP_KEY] = date('Y-m-d H:i:s');
		$params[ApiProtocol::FORMAT_KEY] = $this->format;
		$params[ApiProtocol::SIGN_METHOD_KEY] = $this->sign_method;
		$params[ApiProtocol::VERSION_KEY] = $this->version;
		return $params;
	}
}
