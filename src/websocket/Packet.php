<?php

declare(strict_types=1);

namespace littler\websocket;

use Exception;

class Packet
{
	public const ERROR_MSG = 'FF';

	public const DISPATCH = 0;

	public const MESSAGE = 1;

	public const SYN = 2;

	public const ACK = 3;

	public const PONG = 4;

	public const PING = 5;

	/**
	 * 结束符.
	 */
	public const EOF = 'END';

	/**
	 * 包长度  512kb.
	 */
	public const LEN = 2097152;

	/**
	 * 消息类型 2bits.
	 */
	public $type;

	/**
	 * 协议 2bit 00 消息 admin 99 api 01 shop 02.
	 */
	public $protocol;

	/**
	 * 包长 8bits.
	 */
	public $pack_length;

	/**
	 * 目标   长度 10bits.
	 */
	public $target_length;

	/**
	 * 目标 0 自己.
	 */
	public $target;

	/**
	 * 事件 2bits 长度.
	 */
	public $event_length;

	/**
	 * 具体调度事件.
	 */
	public $event;

	/**
	 * 包体.
	 */
	public $body = 0;

	/**
	 * 数据包格式空事件 空目标 type 05 protocol 00 pack_length 00000000 target_length 00000001 target 0 event_length 01 event 0 body 0
	 * 数据包格式 type 03 protocol pack_length target_length 00000001 0 00
	 * 数据包格式 type 03 protocol pack_length target_length 00000001 0 00.
	 */
	public function __construct($type, $protocol, $pack_length, $target_length, $target, $event_length, $event, $body)
	{
		$this->type = $type;
		$this->protocol = $protocol;
		$this->pack_length = $pack_length;
		$this->target_length = $target_length;
		$this->target = $target;
		$this->event_length = $event_length;
		$this->event = $event;
		$this->body = $body;
	}

	public static function dispatch($payload)
	{
		// return new static(self::DISPATCH, $payload,$pack_length,$target_length,$target,$event_length,$event,$payload);
	}

	/**
	 * 数据打包.
	 */
	public static function pack($type, $protocol, $target, $event='', $body=''): self
	{
		// dd(strlen((string) $target));
		// dd(gettype($type));
		// if (gettype($type)!=='integer') {
		// 	throw new Exception('消息类型错误');
		// }
		if (gettype($protocol)!=='integer') {
			throw new Exception('消息打包失败，未知协议');
		}
		if (! mb_strlen((string) $target)) {
			throw new Exception('缺少目标！');
		}
		$target_length =

		// mb_strlen((new static(
		// 	str_pad((string) $type, 2, '0', STR_PAD_LEFT),
		// 	str_pad((string) $protocol, 2, '0', STR_PAD_LEFT),
		// 	str_pad((string) '', 8, '0', STR_PAD_LEFT),
		// 	str_pad((string) '', 10, '0', STR_PAD_LEFT),
		// 	$target,
		// 	str_pad((string) '', 2, '0', STR_PAD_LEFT),
		// 	'', ''))->toString())
			mb_strlen((string) $target)
		//strlen(json_encode($target, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
;
		$event_length =
		// mb_strlen((new static(
		// 	str_pad((string) $type, 2, '0', STR_PAD_LEFT),
		// 	str_pad((string) $protocol, 2, '0', STR_PAD_LEFT),
		// 	str_pad((string) '', 8, '0', STR_PAD_LEFT),
		// 	str_pad((string) $target_length, 10, '0', STR_PAD_LEFT),
		// 	$target,
		// 	str_pad((string) '', 2, '0', STR_PAD_LEFT),
		// 	'', ''))->toString())
		mb_strlen((string) $event)
		//strlen(json_encode($event, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
;
		// 0400000000340000000001000"PONG"END
		$pack_length = mb_strlen((new static(
			str_pad((string) $type, 2, '0', STR_PAD_LEFT),
			str_pad((string) $protocol, 2, '0', STR_PAD_LEFT),
			str_pad((string) '', 8, '0', STR_PAD_LEFT),
			str_pad((string) $target_length, 10, '0', STR_PAD_LEFT),
			$target,
			str_pad((string) $event_length, 2, '0', STR_PAD_LEFT),
			$event, $body))->toString());
		// 26 +
		// // strlen((string) $body)
		// strlen(json_encode($body, JSON_UNESCAPED_UNICODE))
		// + $target_length+$event_length;

		return new static(
			str_pad((string) $type, 2, '0', STR_PAD_LEFT),
			str_pad((string) $protocol, 2, '0', STR_PAD_LEFT),
			str_pad((string) $pack_length, 8, '0', STR_PAD_LEFT),
			str_pad((string) $target_length, 10, '0', STR_PAD_LEFT),
			$target,
			str_pad((string) $event_length, 2, '0', STR_PAD_LEFT),
			$event, $body);
	}

	/**
	 * 数据解包.
	 */
	public static function unpack(string $packet): self
	{
		if (mb_strlen($packet)<30) {
			throw new Exception('数据包格式不合法');
		}
		$type = substr($packet, 0, 2) ?? null;
		$protocol = substr($packet, 2, 2) ?? null;

		$pack_length = substr($packet, 4, 8) ?? null;

		$target_length = substr($packet, 12, 10) ?? null;

		$target = substr($packet, 22, (int) $target_length) ?? null;

		$event_length = substr($packet, (int) $target_length +22, 2) ?? null;

		$event = substr($packet, (int) $target_length +24, (int) $event_length) ?? '';

		$body = substr($packet, (int) $target_length +24+$event_length, -3) ?? '';
		$end = substr($packet, -3);
		// dd($end);
		return new static(
			(int) str_pad($type, 2, '0', STR_PAD_LEFT),
			(int) str_pad($protocol, 2, '0', STR_PAD_LEFT),
			(int) str_pad($pack_length, 8, '0', STR_PAD_LEFT),
			(int) str_pad($target_length, 10, '0', STR_PAD_LEFT),
			$target,
			(int) str_pad($event_length, 2, '0', STR_PAD_LEFT),
			$event, json_decode($body, true));
	}

	public function toString()
	{
		return $this->type . $this->protocol . $this->pack_length . $this->target_length . $this->target . $this->event_length . $this->event . trim_all(json_encode($this->body, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES))
		. self::EOF;
	}
}
