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

namespace littler\library;

 use think\console\Output;

 class ProgressBar
 {
 	protected $output;

 	protected $total;

 	protected $current = 0;

 	protected $header = '[x] ';

 	protected $length = 100;

 	protected $average;

 	public function __construct(Output $output, int $total)
 	{
 		$this->output = $output;

 		$this->total = $total;

 		$this->average = $this->length / $total;
 	}

 	/**
 	 * 开始.
 	 */
 	public function start()
 	{
 		$this->write();
 	}

 	/**
 	 * 前进.
 	 *
 	 * @param int $step
 	 */
 	public function advance($step = 1)
 	{
 		$this->current += $step;

 		$this->write();
 	}

 	/**
 	 * 结束
 	 */
 	public function finished()
 	{
 		$this->write(true);

 		$this->current = 1;
 	}

 	/**
 	 * 设置头信息.
 	 *
 	 * @param $header
 	 * @return $this
 	 */
 	public function setHeader($header)
 	{
 		$this->header = $header;

 		return $this;
 	}

 	/**
 	 * 输出.
 	 *
 	 * @param bool $end
 	 */
 	protected function write($end = false)
 	{
 		$bar = $this->bar() . ($end ? '' : "\r");

 		$this->output->write(sprintf('<info>%s</info>', $bar), false);
 	}

 	/**
 	 * 进度条
 	 *
 	 * @return string
 	 */
 	protected function bar()
 	{
 		$left = $this->total - $this->current;

 		$empty = str_repeat(' ', intval($left * $this->average));

 		$bar = str_repeat('>', intval($this->current * $this->average));

 		$percent = ((int) (sprintf('%.2f', $this->current / $this->total) * 100)) . '%';

 		return $this->header . $bar . $empty . ' ' . $percent;
 	}
 }
