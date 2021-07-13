<?php

declare(strict_types=1);

namespace littler\websocket;

class Engine
{
	/**
	 * packet type `close`.
	 */
	public const CLOSE = 0;

	/**
	 * packet type `open`.
	 */
	public const OPEN = 1;

	/**
	 * packet type `ping`.
	 */
	public const PING = 2;

	/**
	 * packet type `pong`.
	 */
	public const PONG = 3;

	/**
	 * packet type `message`.
	 */
	public const MESSAGE = 4;

	public $type;

	public $data = '';

	public function __construct($type, $data = '')
	{
		$this->type = $type;
		$this->data = $data;
	}

	public static function open($payload)
	{
		return new static(self::OPEN, $payload);
	}

	public static function pong($payload = '')
	{
		return new static(self::PONG, $payload);
	}

	public static function ping()
	{
		return new static(self::PING);
	}

	public static function message($payload)
	{
		return new static(self::MESSAGE, $payload);
	}
}
