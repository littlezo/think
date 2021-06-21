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
namespace littler\generate\build\types;

use PhpParser\Comment\Doc;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;

class Arr
{
    public function build($fields, $key = 'name', $valve = false)
    {
        $items = [];

        foreach ($fields as $field) {
            // dd($field);
            if ($valve) {
                $arrItem = new ArrayItem(new String_($field[$key]), new String_($field[$valve]));
            } else {
                $arrItem = new ArrayItem(new String_($field[$key]));
            }
            if ($field['comment']) {
                $arrItem->setDocComment(
                    new Doc('// ' . $field['comment'])
                );
            }
            $items[] = $arrItem;
        }

        return new Array_($items);
    }
}
