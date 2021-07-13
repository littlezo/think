<?php

declare(strict_types=1);

namespace littler\websocket;

use Exception;

class Dispatch
{
	private $target;

	private $service;

	private $argv;

	public function __construct($service, $target, $argv)
	{
		$this->service = $service;
		$this->target = $target;
		$this->argv = $argv;
	}

	public static function init($event, $argc)
	{
		$args = '';
		try {
			$args =  json_decode($argc, true);
		} catch (\Throwable $e) {
			$args =  $argc;
		}
		try {
			[$service,$target]= explode('@', $event);
			return new static(service($service),$target, $args);
		} catch (\Throwable $e) {
			throw new Exception('服务或调度调度目标不存在，无法完成调度。请检查格式是否正确，如格式无误，请联系后端查看是否注册服务', 9400500);
		}
	}

	public function handle()
	{
		// dd(...$this->argv);
		try {
			if (! $this->service||! $this->target) {
				throw new Exception('服务或调度调度目标不存在，无法完成调度。请检查格式是否正确，如格式无误，请联系后端查看是否注册服务', 9400500);
			}
			if (is_array($this->argv)) {
				return $this->service->{$this->target}(...$this->argv);
			}
			return $this->service->{$this->target}($this->argv);
		} catch (\Throwable $e) {
			throw new Exception($e->getMessage(), $e->getCode(), $e);
		}
	}
}
