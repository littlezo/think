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

namespace littler\library\excel;

trait MacroExcel
{
	/**
	 * @var string
	 */
	protected $start = 'A';

	/**
	 * 开始行.
	 *
	 * @var int
	 */
	protected $row = 1;

	/**
	 * @var array
	 */
	protected $columns = [];

	/**
	 * 设置内存限制.
	 */
	public function setMemoryLimit()
	{
		if (property_exists($this->excel, 'memory')) {
			ini_set('memory_limit', $this->excel->memory);
		}
	}

	/**
	 * 设置开始的单元.
	 */
	protected function getStartSheet(): string
	{
		if (method_exists($this->excel, 'start')) {
			$this->start = $this->excel->start();
		}

		return $this->start;
	}

	/**
	 * 设置单元格宽度.
	 */
	protected function setSheetWidth()
	{
		if (method_exists($this->excel, 'setWidth')) {
			$width = $this->excel->setWidth();

			foreach ($width as $sheet => $w) {
				$this->getWorksheet()->getColumnDimension($sheet)->setWidth($w);
			}
		}
	}

	/**
	 * before.
	 */
	protected function before()
	{
		if (method_exists($this->excel, 'before')) {
			$this->excel->before();
		}
	}

	/**
	 * 设置 column 信息 ['A', 'B', 'C' ...].
	 *
	 * @return array
	 */
	protected function getSheetColumns()
	{
		if (empty($this->columns)) {
			$start = $this->getStartSheet();

			$columns = [];
			// 通过 headers 推断需要的 columns
			foreach ($this->excel->headers() as $k => $header) {
				$columns[] = chr(ord($start) + $k);
			}

			return $columns;
		}

		return $this->columns;
	}

	/**
	 * set keys.
	 *
	 * @return array
	 */
	protected function getKeys()
	{
		if (method_exists($this->excel, 'keys')) {
			return $this->excel->keys();
		}

		return [];
	}

	/**
	 * set start row.
	 *
	 * @return int
	 */
	protected function getStartRow()
	{
		if (method_exists($this->excel, 'setRow')) {
			$this->row = $this->excel->setRow();
		}

		return $this->row;
	}

	/**
	 * 设置 title.
	 */
	protected function setTitle()
	{
		if (method_exists($this->excel, 'setTitle')) {
			[$cells, $title, $style] = $this->excel->setTitle();

			$this->getWorksheet()
				->mergeCells($cells) // 合并单元格
				->setCellValue(explode(':', $cells)[0], $title)
				->getStyle($cells) // 设置样式
				->getAlignment()
				->setHorizontal($style);
		}
	}

	/**
	 * register worksheet for excel.
	 */
	protected function registerWorksheet()
	{
		if (method_exists($this->excel, 'getWorksheet')) {
			$this->excel->getWorksheet($this->getWorksheet());
		}
	}

	/**
	 * 增加 start row.
	 */
	protected function incRow()
	{
		++$this->row;
	}
}
