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
namespace littler\generate\build\classes;

use PhpParser\BuilderFactory;

class Uses
{
    public function name(string $name, string $as = '')
    {
        $build = (new BuilderFactory())->use($name);

        if ($as) {
            $build->as($as);
        }

        return $build;
    }

    public function function(string $function)
    {
        return (new BuilderFactory())->useFunction($function);
    }

    public function const(string $const)
    {
        return (new BuilderFactory())->useConst($const);
    }
}
