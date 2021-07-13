<?php

declare(strict_types=1);
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace littler\websocket;

use Exception;
use GatewayWorker\Lib\Gateway;
use think\worker\Application;
use Workerman\Worker;

/**
 * Worker 命令行服务类.
 */
class Handler
{
	/**
	 * onWorkerStart 事件回调
	 * 当businessWorker进程启动时触发。每个进程生命周期内都只会触发一次
	 *
	 * @return void
	 */
	public static function onWorkerStart(Worker $businessWorker)
	{
		$app = new Application();
		$app->initialize();
	}

	/**
	 * onConnect 事件回调
	 * 当客户端连接上gateway进程时(TCP三次握手完毕时)触发.
	 *
	 * @param int $client_id
	 * @return void
	 */
	public static function onConnect($client_id)
	{
		// request()
		self::ack(Packet::pack(Packet::MESSAGE, 0, 0, '', $client_id));

		// Gateway::sendToCurrentClient($client_id);
	}

	/**
	 * onWebSocketConnect 事件回调
	 * 当客户端连接上gateway完成websocket握手时触发.
	 *
	 * @param int $client_id 断开连接的客户端client_id
	 * @param mixed $data
	 * @return void
	 */
	public static function onWebSocketConnect($client_id, $data)
	{
		self::ack(Packet::pack(Packet::MESSAGE, 0, 0, '', $data));
		// Gateway::sendToAll(json_encode($data));
	}

	/**
	 * onMessage 事件回调
	 * 当客户端发来数据(Gateway进程收到数据)后触发.
	 *
	 * @param int $client_id
	 * @param mixed $data
	 * @return void
	 */
	public static function onMessage($client_id, $data)
	{
		// Gateway::sendToAll(json_encode($data));/
		try {
			$packet = Packet::unpack($data);
			switch ($packet->type) {
				case Packet::DISPATCH:
					self::ack(Packet::pack(Packet::DISPATCH, 0, 0, '', Dispatch::init($packet->event, $packet->body)->handle()));
					break;
				case Packet::MESSAGE:
					self::push(Packet::pack(Packet::MESSAGE, 0, 0, '', $packet->body));
					break;
				case Packet::PONG:
					self::ack(Packet::pack(Packet::PING, 0, 0, '', 'PING'));
					break;
				case Packet::SYN:
					self::ack(Packet::pack(Packet::ACK, 0, 0, '', $packet->body));
					break;
				case Packet::PING:
					self::ack(Packet::pack(Packet::PONG, 0, 0, '', 'PONG'));
					break;
				default:
					self::ack(Packet::pack(Packet::ERROR_MSG, 0, 0, '', '消息格式不合法'));
					break;
			}
		} catch (\Throwable $e) {
			Gateway::sendToClient($client_id, Packet::pack(
				Packet::ERROR_MSG,
				0,
				0,
				'',
				['code' => $e->getCode(),
				'error' => $e->getMessage(),
				'trace' => $e->getTrace(), ]
			)->toString());
		}
	}

	/**
	 * onClose 事件回调 当用户断开连接时触发的方法.
	 *
	 * @param int $client_id 断开连接的客户端client_id
	 * @return void
	 */
	public static function onClose($client_id)
	{
		GateWay::sendToAll("client[$client_id] logout\n");
	}

	/**
	 * onWorkerStop 事件回调
	 * 当businessWorker进程退出时触发。每个进程生命周期内都只会触发一次。
	 *
	 * @return void
	 */
	public static function onWorkerStop(Worker $businessWorker)
	{
		echo "WorkerStop\n";
	}

	protected static function push($data, $target = 0, $group = false, $raw = true)
	{
		if (! $target) {
			return false;
		}
		if (! $data instanceof Packet) {
			throw new Exception('消息类型不规范');
		}
		if ($group) {
			if (is_array($target)) {
				foreach ($target as $target_group) {
					Gateway::sendToGroup($target_group, $data->toString(), $raw);
				}
			} else {
				Gateway::sendToGroup($target, $data->toString(), $raw);
			}
		} else {
			if (is_array($target)) {
				foreach ($target as $target_uid) {
					Gateway::sendToGroup($target_uid, $data->toString(), $raw);
				}
			} else {
				Gateway::sendToUid($target, $data->toString(), $raw);
			}
		}
	}

	protected static function ack($data, $raw = true)
	{
		if (! $data instanceof Packet) {
			throw new Exception('消息类型不规范');
		}
		Gateway::sendToCurrentClient($data->toString(), $raw);
	}
}
