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
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Factory
{
	public static function make($type, $spreadsheet)
	{
		if ($type === 'xlsx') {
			return app(Xlsx::class)->setSpreadsheet($spreadsheet);
		}

		if ($type === 'xls') {
			return new Xls($spreadsheet);
		}

		if ($type === 'csv') {
			return (new Csv($spreadsheet))->setUseBOM('utf-8');
		}

		throw new FailedException(sprintf('Type [%s] not support', $type));
	}
}
