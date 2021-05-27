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

class Classes
{
    protected $classBuild;

    public function __construct(string $name, string $type = 'class')
    {
        if ($type == 'trait') {
            $this->classBuild = (new BuilderFactory())->trait($name);
        } else {
            $this->classBuild = (new BuilderFactory())->class($name);
        }
    }

    /**
     * 设置 comment.
     *
     * @param string $comment
     * @return $this
     */
    public function docComment($comment = "\r\n")
    {
        $this->classBuild->setDocComment($comment);

        return $this;
    }

    /**
     * @param $extend
     * @return $this
     */
    public function extend($extend)
    {
        $this->classBuild->extend($extend);

        return $this;
    }

    /**
     * @param $interfaces
     * @return $this
     */
    public function implement($interfaces)
    {
        $this->classBuild->implement($interfaces);

        return $this;
    }

    /**
     * @return $this
     */
    public function abstract()
    {
        $this->classBuild->makeAbstract();

        return $this;
    }

    /**
     * @return $this
     */
    public function final()
    {
        $this->classBuild->makeFinal();

        return $this;
    }

    public function build()
    {
        return $this->classBuild;
    }

    public function addMethod(Methods $method)
    {
        $this->classBuild->addStmt($method->build());

        return $this;
    }

    public function addProperty(Property $property)
    {
        $this->classBuild->addStmt($property->build());

        return $this;
    }

    public function addTrait(Traits $trait)
    {
        $this->classBuild->addStmt($trait->build());

        return $this;
    }

    /**
     * when.
     *
     * @param $condition
     * @return $this
     */
    public function when($condition, \Closure $closure)
    {
        if ($condition) {
            $closure($this);
        }

        return $this;
    }
}
