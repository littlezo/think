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
namespace littler\generate\build;

use littler\App;
use littler\facade\FileSystem;
use littler\generate\build\classes\Classes;
use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter\Standard;

class Build
{
    protected $astBuilder;

    protected $outPath;

    protected $filename;

    public function __construct()
    {
        $this->astBuilder = app(BuilderFactory::class);
    }

    /**
     * 命名空间.
     *
     * @return $this
     */
    public function namespace(string $namespace)
    {
        $this->astBuilder = $this->astBuilder->namespace($namespace);

        return $this;
    }

    /**
     * use 方法体.
     *
     * @param $use
     * @return $this
     */
    public function use($use)
    {
        $this->astBuilder->addStmt($use);

        return $this;
    }

    /**
     * class 模版.
     *
     * @return $this
     */
    public function class(Classes $class, \Closure $function)
    {
        $function($class);

        $this->astBuilder->addStmt($class->build());

        return $this;
    }

    /**
     * 条件.
     *
     * @param $condition
     * @return $this
     */
    public function when($condition, \Closure $closure)
    {
        if ($condition && $closure instanceof \Closure) {
            $closure($this);
        }

        return $this;
    }

    /**
     * 获取内容.
     *
     * @return string
     */
    public function getContent()
    {
        $stmts = [$this->astBuilder->getNode()];

        $prettyPrinter = new Standard();

        return $prettyPrinter->prettyPrintFile($stmts);
    }

    /**
     * 输出.
     *
     * @return string
     */
    public function output()
    {
        return FileSystem::put($this->outPath . $this->filename, $this->getContent());
    }

    /**
     * 输出 Path.
     *
     * @param $path
     * @return $this
     */
    public function path($path)
    {
        App::makeDirectory($path);

        $this->outPath = $path;

        return $this;
    }

    /**
     * 设置文件名.
     *
     * @param $name
     * @return mixed
     */
    public function filename($name)
    {
        $this->filename = $name;

        return $this;
    }
}
