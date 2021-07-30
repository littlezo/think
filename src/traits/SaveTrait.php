<?php

/**
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。
 * ## 只要思想不滑稽，方法总比苦难多！
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @see     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 */

declare(strict_types=1);

namespace littler\traits;

use littler\annotation\Route;
use littler\Request;
use littler\Response;

/**
 * desc 禁止在此写业务逻辑，执行生成后将被覆盖.
 */
trait SaveTrait
{
	/**
	 * @Route("/", method="POST", ignore_verify=false)
	 * @apiDocs({
	 *     "title": "添加",
	 *     "version": "v1.0.0",
	 *     "name": "save",
	 *     "headers": {
	 *         "Authorization": "Bearer Token",
	 *     },
	 *     "desc": "查询参数详见快速查询 字段含义参加字段映射",
	 *     "success": {
	 *         "code": 200,
	 *         "type": "success",
	 *         "message": "成功消息||success",
	 *         "timestamp": 1234567890,
	 *         "result": {
	 *             "encryptData": "加密数据自行解密",
	 *         },
	 *     },
	 *     "error": {
	 *         "code": 500,
	 *         "message": "错误消息",
	 *         "type": "error",
	 *         "result": "",
	 *         "timestamp": 1234567890,
	 *     },
	 *     "param": {
	 *
	 *     }
	 * })
	 * @return \think\Response
	 */
	public function save(Request $request): ?\think\Response
	{
		return Response::success($this->service->save($request->post()));
	}
}
