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

class Traits
{
    protected $traitBuild;

    protected $build;

    public function use(...$names)
    {
        $this->build = new BuilderFactory();

        $this->traitBuild = call_user_func_array([$this->build, 'useTrait'], $names);

        return $this;
    }

    /**
     * 设置 comment.
     *
     * @param string $comment
     * @return $this
     */
    public function docComment($comment = "\r\n")
    {
        $this->traitBuild->setDocComment($comment);

        return $this;
    }

    public function and($name)
    {
        dd($name);
        $this->traitBuild->and($name);

        return $this;
    }

    /**
     * with.
     *
     * @return $this
     */
    public function with(\Closure $closure = null)
    {
        if ($closure instanceof \Closure) {
            $this->traitBuild->withe($closure($this));

            return $this;
        }

        return $this;
    }

    /**
     * @param $name
     * @param null $method
     * @return $this
     */
    public function adaptation($name, $method = null)
    {
        $this->build = $this->build->traitUseAdaptation($name . $method);

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function as($name)
    {
        $this->build->as($name);

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function insteadof($name)
    {
        $this->build->insteadof($name);

        return $this;
    }

    public function build()
    {
        return $this->traitBuild;
    }
}
