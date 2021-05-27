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

use littler\generate\build\traits\MethodReturn;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Expression;

class Methods
{
    use MethodReturn;

    protected $methodBuild;

    public function __construct(string $name)
    {
        $this->methodBuild = (new BuilderFactory())->method($name);
    }

    public function public()
    {
        $this->methodBuild->makePublic();

        return $this;
    }

    public function protected()
    {
        $this->methodBuild->makeProtected();

        return $this;
    }

    public function private()
    {
        $this->methodBuild->makePrivate();

        return $this;
    }

    /**
     * set params.
     *
     * @param $type
     * @param $param
     * @param $default
     * @return $this
     */
    public function param($param, $type = null, $default = null)
    {
        $param = (new BuilderFactory())->param($param);

        if ($type) {
            $param = $param->setType($type);
        }

        if ($default) {
            $param = $param->setDefault($default);
        }

        $this->methodBuild->addParam(
            $param
        );

        return $this;
    }

    /**
     * 定义.
     *
     * @param $variable
     * @param $value
     * @return $this
     */
    public function declare($variable, $value)
    {
        $smt = new Expression(
            new Assign(
                new PropertyFetch(
                    new Variable('this'),
                    new Identifier($variable)
                ),
                new Variable($value)
            )
        );

        $this->methodBuild->addStmt($smt);

        return $this;
    }

    /**
     * 返回值
     *
     * @param $returnType
     * @return $this
     */
    public function returnType($returnType)
    {
        $this->methodBuild->setReturnType($returnType);

        return $this;
    }

    /**
     * 注释.
     *
     * @param $comment
     * @return $this
     */
    public function docComment(string $comment)
    {
        $this->methodBuild->setDocComment($comment);

        return $this;
    }

    /**
     * 抽象
     *
     * @return $this
     */
    public function toAbstract()
    {
        $this->methodBuild->makeAbstract();

        return $this;
    }

    /**
     * final.
     *
     * @return $this
     */
    public function toFinal()
    {
        $this->methodBuild->makeFinal();

        return $this;
    }

    public function build()
    {
        return $this->methodBuild;
    }
}
