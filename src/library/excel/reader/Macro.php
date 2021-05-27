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

trait Macro
{
    /**
     * 移除不需要的列.
     *
     * @param ...$indexes
     * @return mixed
     */
    public function remove(...$indexes)
    {
        foreach ($indexes as $index) {
            unset($this->sheets[$index]);
        }

        return $this;
    }

    /**
     * 设置 memory.
     *
     * @return mixed
     */
    public function memory(int $memory)
    {
        ini_set('memory_limit', $memory . 'M');

        return $this;
    }

    /**
     * 处理.
     */
    protected function dealWith(): array
    {
        $headers = $this->headers();

        $data = [];

        foreach ($this->sheets as &$sheet) {
            $d = [];
            foreach ($headers as $k => $header) {
                $d[$header] = method_exists($this, 'deal' . ucfirst($header)) ?

                        $this->{'deal' . ucfirst($header)}($sheet) : $sheet[$k];
            }

            $data[] = $d;
        }

        return $data;
    }
}
