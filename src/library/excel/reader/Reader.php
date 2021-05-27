<?php

declare(strict_types=1);
/**
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。.
 *
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @link     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 *
 */
namespace littler\library\excel\reader;

use littler\Upload;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

abstract class Reader
{
    use Macro;

    /**
     * 当前的 sheet.
     *
     * false 代表获取全部 sheets
     *
     * @var bool
     */
    protected $active = true;

    protected $sheets;

    /**
     * 导入.
     *
     * @param $file
     */
    public function import($file): Reader
    {
        $file = (new Upload())->setPath('excel')->toLocal($file);

        $reader = Factory::make($file);
        // 设置只读
        $reader->setReadDataOnly(true);

        /* @var $spreadsheet Spreadsheet */
        $spreadsheet = $reader->load($file);

        if ($this->active) {
            $this->sheets = $spreadsheet->getActiveSheet()->toArray();
        } else {
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $this->sheets[] = $sheet->toArray();
            }
        }

        return $this;
    }

    /**
     * 必须实现的方法.
     *
     * @return mixed
     */
    abstract public function headers();

    /**
     * 数据处理.
     *
     * @return mixed
     */
    public function then(callable $callback)
    {
        return $callback($this->dealWith());
    }
}
