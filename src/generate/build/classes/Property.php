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

class Property
{
    protected $propertyBuild;

    public function __construct(string $name)
    {
        $this->propertyBuild = (new BuilderFactory())->property($name);
    }

    /**
     * @return $this
     */
    public function public()
    {
        $this->propertyBuild->makePublic();

        return $this;
    }

    /**
     * @return $this
     */
    public function protected()
    {
        $this->propertyBuild->makeProtected();

        return $this;
    }

    /**
     * @return $this
     */
    public function private()
    {
        $this->propertyBuild->makePrivate();

        return $this;
    }

    /**
     * 注释.
     *
     * @param $comment
     * @return $this
     */
    public function static($comment)
    {
        $this->propertyBuild->makeStatic();

        return $this;
    }

    /**
     * set default.
     *
     * @param $value
     * @return $this
     */
    public function default($value)
    {
        $this->propertyBuild->setDefault($value);

        return $this;
    }

    public function type($type)
    {
        $this->propertyBuild->setType($type);

        return $this;
    }

    public function docComment($comment)
    {
        $this->propertyBuild->setDocComment($comment);

        return $this;
    }

    public function build()
    {
        return $this->propertyBuild;
    }
}
