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
namespace littler;

use littler\library\table\Table;

abstract class Table
{
    /**
     * 渲染.
     *
     * @param $only  => form || table
     * @return array|\think\response\Json
     */
    public function render($only)
    {
        if ($only) {
            return Response::success([
                $only => $this->{$only}(),
            ]);
        }

        return Response::success([
            'table' => $this->table(),
            'form' => $this->form(),
        ]);
    }

    abstract protected function table();

    abstract protected function form();

    /**
     * 获取表对象
     */
    protected function getTable(string $tableName): Table
    {
        return new Table($tableName);
    }
}
