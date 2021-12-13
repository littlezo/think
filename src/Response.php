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

namespace littler;

use think\Paginator;
use think\response\Json;

class Response extends \think\Response
{
	/**
	 * API 成功的响应.
	 *
	 * @param array $data
	 * @param $msg
	 * @param int $code
	 */
	public static function api($data = [], $msg = 'success', $code = Code::SUCCESS): Json
	{
		return json([
			'code' => $code,
			'type' => 'success',
			'timestamp' => time(),
			'result' => $data,
			'message' => $msg,
		]);
	}

	/**
	 * 成功的响应.
	 *
	 * @param array $data
	 * @param $msg
	 * @param int $code
	 */
	public static function success($data = [], $msg = 'success', $code = Code::SUCCESS): Json
	{
		return json([
			'code' => $code,
			'type' => 'success',
			'timestamp' => time(),
			'result' => $data,
			'message' => $msg,
		]);
	}

	/**
	 * 分页.
	 *
	 * @param mixed $list
	 * @return
	 */
	public static function paginate($list)
	{
		// dd($list);
		if ($list instanceof Paginator) {
			return json([
				'code' => Code::SUCCESS,
				'message' => 'success',
				'timestamp' => time(),
				'type' => 'success',
				'result' => [
					'total' => $list->total(),
					'page' => $list->currentPage(),
					'size' => $list->listRows(),
					'items' => $list->getCollection(),
				],
			]);
		}
		$list['code'] = Code::SUCCESS;
		$list['message'] = 'success';
		$list['type'] = 'success';
		$list['timestamp'] = time();
		return json($list);
	}

	/**
	 * 错误的响应.
	 *
	 * @param string $msg
	 * @param int $code
	 */
	public static function fail($msg = '', $code = Code::FAILED): Json
	{
		return json([
			'code' => $code,
			'message' => $msg,
			'type' => 'error',
			'result' => '',
			'timestamp' => time(),
		]);
	}

	/**
	 * 文件响应.
	 */
	public static function file($file, string $filename=null, string $mimeType=null, bool $content = false, bool $force =false)
	{
		// $content= file_get_contents($file);
		$response =  Response::create($file, 'file');
		if ($mimeType) {
			$response->mimeType($mimeType);
			$response->contentType($mimeType);
		}
		$response->isContent($content);
		// $response->content($content);
		$response->name($filename);
		$response->force($force);
		return $response;
	}
}
