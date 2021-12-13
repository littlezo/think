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

use littler\exceptions\FailedException;
use littler\Upload;
use littler\Utils;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use think\file\UploadedFile;
use think\helper\Str;

class Excel
{
	use MacroExcel;

	/**
	 * @var ExcelContract
	 */
	protected $excel;

	protected $sheets;

	protected $spreadsheet;

	protected $extension = 'xlsx';

	/**
	 * save.
	 *
	 * @param $path
	 * @param string $disk
	 * @throws Exception
	 * @return string[]
	 */
	public function save(ExcelContract $excel, $path, $disk = 'local'): array
	{
		$this->excel = $excel;

		$this->init();

		! is_dir($path) && mkdir($path, 0777, true);

		$file = $path . date('YmdHis') . Str::random(6) . '.' . $this->extension;

		Factory::make($this->extension, $this->spreadsheet)->save($file);

		if (! file_exists($file)) {
			throw new FailedException($file . ' generate failed');
		}

		if ($disk) {
			$file = $this->upload($disk, $file);
		}

		return ['url' => $file];
	}

	/**
	 * set extension.
	 *
	 * @param $extension
	 * @return $this
	 */
	public function setExtension($extension): Excel
	{
		$this->extension = $extension;

		return $this;
	}

	/**
	 * init excel.
	 *
	 * @throws Exception
	 */
	protected function init()
	{
		$this->setMemoryLimit();
		// register worksheet for current excel
		$this->registerWorksheet();
		// before save excel
		$this->before();
		// set excel title
		$this->setTitle();
		// set excel headers
		$this->setExcelHeaders();
		// set cell width
		$this->setSheetWidth();
		// set worksheets
		$this->setWorksheets();
	}

	/**
	 *  设置 sheets.
	 *
	 * @throws Exception
	 */
	protected function setWorksheets()
	{
		$keys = $this->getKeys();

		$isArray = $this->arrayConfirm();

		$worksheet = $this->getWorksheet();

		if (empty($keys)) {
			if ($isArray) {
				foreach ($this->excel->sheets() as $sheet) {
					$worksheet->fromArray($sheet, null, $this->start . $this->row);
					$this->incRow();
				}
			} else {
				foreach ($this->excel->sheets() as $sheet) {
					$worksheet->fromArray($sheet->toArray(), null, $this->start . $this->row);
					$this->incRow();
				}
			}
		} else {
			if ($isArray) {
				foreach ($this->excel->sheets() as $sheet) {
					$worksheet->fromArray($this->getValuesByKeys($sheet, $keys), null, $this->start . $this->row);
					$this->incRow();
				}
			} else {
				foreach ($this->excel->sheets() as $sheet) {
					$worksheet->fromArray($this->getValuesByKeys($sheet->toArray(), $keys), null, $this->start . $this->row);
					$this->incRow();
				}
			}
		}
	}

	/**
	 * 判断 sheet 是否是 array 类型.
	 *
	 * @return bool
	 */
	protected function arrayConfirm()
	{
		$sheets = $this->excel->sheets();

		$array = true;

		foreach ($sheets as $sheet) {
			$array = is_array($sheet);
			break;
		}

		return $array;
	}

	/**
	 * 获取 item 特定 key 的值
	 *
	 * @return array
	 */
	protected function getValuesByKeys(array $item, array $keys)
	{
		$array = [];

		foreach ($keys as $key) {
			$array[] = $item[$key];
		}

		return $array;
	}

	/**
	 * 设置 Excel 头部.
	 *
	 * @throws Exception
	 */
	protected function setExcelHeaders()
	{
		$worksheet = $this->getWorksheet();

		// get columns
		$columns = $this->getSheetColumns();

		// get start row
		$startRow = $this->getStartRow();

		foreach ($this->excel->headers() as $k => $header) {
			$worksheet->getCell($columns[$k] . $startRow)->setValue($header);
		}

		$this->incRow();
	}

	/**
	 *  get spreadsheet.
	 *
	 * @return Spreadsheet
	 */
	protected function getSpreadsheet()
	{
		if (! $this->spreadsheet) {
			$this->spreadsheet = new Spreadsheet();
		}

		return $this->spreadsheet;
	}

	/**
	 * 获取 active sheet.
	 *
	 * @throws Exception
	 * @return Worksheet
	 */
	protected function getWorksheet()
	{
		return $this->getSpreadsheet()->getActiveSheet();
	}

	/**
	 * upload.
	 *
	 * @param $disk
	 * @param $path
	 * @throws \Exception
	 * @return string
	 */
	protected function upload($disk, $path)
	{
		if ($disk == 'local') {
			return $this->local($path);
		}
		$upload = new Upload();

		return ($disk ? $upload->setDriver($disk) : $upload)->upload($this->uploadedFile($path));
	}

	/**
	 * 返回本地下载地址
	 *
	 * @param $path
	 * @return mixed
	 */
	protected function local($path)
	{
		return \config('filesystem.disks.local')['domain'] . '/' .
			str_replace('\\', '/', str_replace(Utils::publicPath(), '', $path));
	}

	/**
	 *  get uploaded file.
	 *
	 * @param $file
	 * @return UploadedFile
	 */
	protected function uploadedFile($file)
	{
		return new UploadedFile($file, pathinfo($file, PATHINFO_BASENAME));
	}
}
