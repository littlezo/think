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
namespace littler\generate\build\traits;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Return_;

trait MethodReturn
{
    /**
     * 列表.
     *
     * @param $model
     * @return $this
     */
    public function index($model)
    {
        $class = new Name('Response');

        $arg = new Arg(new MethodCall(
            new PropertyFetch(
                new Variable('this'),
                new Identifier($model)
            ),
            new Identifier('getList')
        ));

        $this->methodBuild->addStmt(new Return_(new StaticCall($class, 'paginate', [$arg])));

        return $this;
    }

    /**
     * 保存.
     *
     * @param $model
     * @return $this
     */
    public function save($model)
    {
        $arg = new Arg(new MethodCall(
            new PropertyFetch(
                new Variable('this'),
                new Identifier($model)
            ),
            new Identifier('storeBy'),
            [new Arg(new MethodCall(new Variable('request'), new Identifier('post')))]
        ));

        $class = new Name('Response');

        $this->methodBuild->addStmt(new Return_(new StaticCall($class, 'success', [$arg])));

        return $this;
    }

    /**
     * 更新.
     *
     * @param $model
     * @return $this
     */
    public function update($model)
    {
        $arg = new Arg(new MethodCall(
            new PropertyFetch(
                new Variable('this'),
                new Identifier($model)
            ),
            new Identifier('updateBy'),
            [
                new Arg(new Variable('id')),
                new Arg(new MethodCall(new Variable('request'), new Identifier('post'))),
            ]
        ));

        $class = new Name('Response');

        $this->methodBuild->addStmt(new Return_(new StaticCall($class, 'success', [$arg])));

        return $this;
    }

    public function read($model)
    {
        $arg = new Arg(new MethodCall(
            new PropertyFetch(
                new Variable('this'),
                new Identifier($model)
            ),
            new Identifier('findBy'),
            [
                new Arg(new Variable('id')),
            ]
        ));

        $class = new Name('Response');

        $this->methodBuild->addStmt(new Return_(new StaticCall($class, 'success', [$arg])));

        return $this;
    }

    /**
     * 删除.
     *
     * @param $model
     * @return $this
     */
    public function delete($model)
    {
        $arg = new Arg(new MethodCall(
            new PropertyFetch(
                new Variable('this'),
                new Identifier($model)
            ),
            new Identifier('deleteBy'),
            [
                new Arg(new Variable('id')),
            ]
        ));

        $class = new Name('Response');

        $this->methodBuild->addStmt(new Return_(new StaticCall($class, 'success', [$arg])));

        return $this;
    }
}
