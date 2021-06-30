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

namespace littler\library\excel\reader;

use littler\exceptions\FailedException;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Ods;
use PhpOffice\PhpSpreadsheet\Reader\Slk;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xml;

class Factory
{
	/**
	 * make reader.
	 *
	 * @param $filename
	 * @return mixed
	 */
	public static function make($filename)
	{
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		if (isset(self::readers()[$ext])) {
			return app()->make(self::readers()[$ext]);
		}

		throw new FailedException('Dont Support The File Extension');
	}

	/**
	 * readers.
	 *
	 * @return string[]
	 */
	protected static function readers(): array
	{
		return [
			'xlsx' => Xlsx::class,
			'xml' => Xml::class,
			'ods' => Ods::class,
			'slk' => Slk::class,
			'csv' => Csv::class,
		];
	}
}
