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
namespace littler\library;

use littler\facade\FileSystem;
use think\exception\ClassNotFoundException;
use think\helper\Str;

class ParseClass
{
    protected $path;

    protected $namespace;

    protected $module;

    protected $controller;

    /**
     * 获取父类方法.
     *
     * @throws \ReflectionException
     * @return array
     */
    public function parentMethods()
    {
        $class = $this->getClass();

        $parent = $class->getParentClass();

        $methods = [];

        foreach ($parent->getMethods() as $method) {
            if (! $this->isMagicMethod($method->getName())) {
                $methods[] = $method->getName();
            }
        }

        return $methods;
    }

    /**
     * 获取所有方法.
     *
     * @throws \ReflectionException
     * @return array
     */
    public function methods()
    {
        $class = $this->getClass();

        $methods = [];

        foreach ($class->getMethods() as $method) {
            if (! $this->isMagicMethod($method->getName())) {
                $methods[] = $method->getName();
            }
        }

        return $methods;
    }

    /**
     * @throws \ReflectionException
     * @return mixed
     */
    public function onlySelfMethods()
    {
        $methods = [];

        $parentMethods = $this->parentMethods();

        foreach ($this->methods() as $method) {
            if (! in_array($method, $parentMethods)) {
                $methods[] = $method;
            }
        }

        return $methods;
    }

    /**
     * 获取所有class.
     *
     * @param string $layer 层名 controller model ...
     * @throws \ReflectionException
     * @return \ReflectionClass
     */
    public function getClassMethod($layer = 'controller')
    {
        $class_file = FileSystem::allFiles($this->path . DIRECTORY_SEPARATOR . $layer . DIRECTORY_SEPARATOR);
        $class_list = [];
        foreach ($class_file as $item) {
            if ($item->getExtension() === 'php') {
                $filename = str_replace('.php', '', str_replace(__DIR__, '', $item->getFilename()));
                $class = $this->namespace . $this->module . '\\controller\\' . ucfirst($filename);
                // [$controller, $action] = explode(Str::contains($rule, '@') ? '@' : '/', $rule);
                // $docComment = (new \ReflectionClass($controller))->getMethod($action)->getDocComment();
                // return strpos($docComment, config('little.permissions.method_auth_mark')) !== false;
                if (class_exists($class)) {
                    $this->controller = Str::snake($filename);
                    $methods = [];
                    $reflectionClass = new \ReflectionClass($class);
                    foreach ($reflectionClass->getMethods() as $method) {
                        if (! $this->isMagicMethod($method->getName()) && $method->isPublic() && $method->getName() !== 'initialize' && $method->getName() !== 'validate') {
                            $methods[] = Str::snake($method->getName());
                        }
                    }
                    $class_list = array_merge(
                        $class_list,
                        [$this->namespace . $this->module . '\\' . $this->controller => [
                            'class' => $class,
                            'methods' => $methods,
                        ]]
                    );
                }
            }
        }
        return $class_list;
    }

    /**
     * 获取class.
     *
     * @param mixed $layer
     * @throws \ReflectionException
     * @return \ReflectionClass
     */
    public function getClass($layer = 'controller')
    {
        // $class = $this->parse(ucfirst($this->controller));
        $class = $this->namespace . $this->module . '\\' . $layer . '\\' . ucfirst($this->controller);

        if (class_exists($class)) {
            return new \ReflectionClass($class);
        }

        throw new ClassNotFoundException($class . ' not found');
    }

    /**
     * @param $module
     * @param mixed $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $psr4 = (new Composer())->psr4Autoload();
        $packagesPsr4 = (new Composer())->packagesPsr4Autoload();
        foreach ($psr4 as $key => $_namespace) {
            if ($_namespace == $namespace) {
                $this->namespace = $key;
                break;
            }
        }
        if (is_null($this->namespace)) {
            foreach ($packagesPsr4 as $key => $_namespace) {
                if ($namespace == $key) {
                    $this->namespace = $key;
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * @param $module
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @param $module
     * @param $controller
     * @return $this
     */
    public function setRule($module, $controller)
    {
        $this->module = $module;

        $this->controller = $controller;

        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * 解析应用类的类名.
     * @param string $layer 层名 controller model ...
     * @param string $name 类名
     */
    public function parse(string $layer, string $name): string
    {
        $name = str_replace(['/', '.', '@'], '\\', $name);
        $array = explode('\\', $name);
        $class = Str::studly(array_pop($array));
        $path = $array ? implode('\\', $array) . '\\' : '';
        return $this->namespace . $this->module . '\\' . $layer . '\\' . $path . $class;
    }

    /**
     * @param $method
     * @return bool
     */
    protected function isMagicMethod($method)
    {
        return strpos($method, '__') !== false;
    }
}
