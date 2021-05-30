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
use ReflectionMethod;
use think\exception\ClassNotFoundException;
use think\helper\Str;

class ParseClass
{
    protected $path;

    protected $namespace;

    protected $module;

    protected $controller;

    public static function directory(): string
    {
        return app()->getRootPath();
    }
    /**
     * 获取根命名空间
     * @param $module
     */
    public static function getRootNamespace($path): ?string
    {
        $composer = new Composer;
        $psr4Autoload =  $composer->psr4Autoload();
        $packagesPsr4Autoload =  $composer->packagesPsr4Autoload();
        $psr4 = array_merge($psr4Autoload, $packagesPsr4Autoload);
        $namespace = null;
        foreach ($psr4 as $_namespace => $item) {
            foreach ($item as $_path) {
                if (!is_bool(stripos($path, $_path))) {
                    $namespace = $_namespace;
                    continue;
                }
            }
        }
        return $namespace;
    }
    /**
     * 获取所有class.
     *
     * @param string $layer 层名 controller model ...
     * @throws \ReflectionException
     * @return \ReflectionClass
     */
    public function getAllClass($layer = 'controller')
    {
        $class_file = FileSystem::allFiles(root_path());
        $class_list = [];
        foreach ($class_file as $item) {
            $path = $item->getPath();
            $relative_path= str_replace(root_path(), '', $path);
            $pos = stripos($relative_path, $layer);
            $file_name = $item->getFilename();
            if (!$pos) {
                continue;
            }
            if ($item->getExtension() !== 'php') {
                continue;
            }
            $short_path = substr($relative_path, 0, $pos-1);
            $module = substr($short_path, (int) strrpos($short_path, '/'));
            if (!is_bool(strpos($module, '/'))) {
                $module = substr($module, 1, ) ;
            }

            // dd($module);
            $namespace =$this->getRootNamespace($relative_path)??false;
            if (!$namespace) {
                continue;
            }
            // dd($module);
            // dd($namespace);
            $class_name = str_replace('.php', '', $file_name);
            $class = str_replace(['/', '\\\\'], '\\', $namespace . $module. '\\' .$layer. '\\' .$class_name);
            try {
                if (class_exists($class)) {
                    $class_list[] = [
                     'class'=>$class,
                     'path'=>$relative_path .DIRECTORY_SEPARATOR . $file_name,
                 ];
                }
            } catch (\Throwable $t) {
                continue;
            }
        }
        return $class_list;
    }
    /**
     * 获取所有class.
     *
     * @param string $layer 层名 controller model ...
     * @throws \ReflectionException
     * @return \ReflectionClass
     */
    public function getRoutes()
    {
        dd($this->getAllClass());
        $layer = 'controller';
        // dd($this->path);
        // $class_file = FileSystem::allFiles($this->path  . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $layer) . DIRECTORY_SEPARATOR);
        $class_file = FileSystem::allFiles(root_path());
        // dd($class_file);
        $class_list = [];
        // dd($class_file);
        foreach ($class_file as $item) {
            $pos = stripos($item->getPath(), $layer);
            if (!$pos) {
                continue;
            }
            dd($item->getPath());

            if ($item->getPath()) {
            }
            if ($item->getExtension() === 'php') {
                dd($item->getPath());
                $class_name = str_replace('.php', '', str_replace(__DIR__, '', $item->getFilename()));
                $class = $this->namespace . $this->module . '\\' . str_replace(['/', '\\'], '\\', $layer) . '\\' . ucfirst($class_name);
                if (class_exists($class)) {
                    $this->controller = Str::snake($class_name);
                    $methods = [];
                    $reflectionClass = new \ReflectionClass($class);
                    //通过反射获取类的注释
                    $doc = $reflectionClass->getDocComment();
                    //解析类的注释头
                    $ParseDoc = new ParseDoc();
                    $paras_result = $ParseDoc->parse($doc);
                    $class_docs = [
                        'title' => $paras_result['title'] ?? '',
                        'group' => $paras_result['group'] ?? '',
                        'resource' => $paras_result['resource'] ?? '',
                        'version' => $paras_result['version'] ?? '',
                    ];
                    // dd($class_docs);
                    $method_docs = [];
                    foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                        //输出测试
                        if (! $this->isMagicMethod($method->getName()) && $method->isPublic() && $method->getName() !== 'initialize' && $method->getName() !== 'validate') {
                            $method_doc = $method->getDocComment();
                            //解析注释
                            $info = $ParseDoc->parse($method_doc);
                            $route = [
                                'title' => $info['title'] ?? '',
                                'group' => $info['group'] ?? '',
                                'resource' => $info['resource'] ?? '',
                                'version' => $info['version'] ?? '',
                                'param' => $info['param'] ?? [],
                                'route' => $info['route'] ?? '',
                            ];
                            $method_docs[] = $route;
                            //获取方法的参数
                            $params = $method->getParameters();
                            foreach ($params as $param) {
                                //参数是否设置了默认参数，如果设置了，则获取其默认值
                                $arguments[$param->getName()] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                            }
                            $methods[] = Str::snake($method->getName());
                        }
                    }
                    // dd($method_docs);
                    $class_docs['method'] = $method_docs;
                    $class_docs['class'] = $class_name;
                    $class_docs['namespace'] = $reflectionClass->getNamespaceName();
                    $class_list[] = $class_docs;
                }
            }
            // dd($class_list);
        }
        return $class_list;
    }

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
        $class_file = FileSystem::allFiles($this->path . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $layer) . DIRECTORY_SEPARATOR);
        $class_list = [];
        foreach ($class_file as $item) {
            if ($item->getExtension() === 'php') {
                $class_name = str_replace('.php', '', str_replace(__DIR__, '', $item->getFilename()));
                $class = $this->namespace . $this->module . '\\' . str_replace(['/', '\\'], '\\', $layer) . '\\' . ucfirst($class_name);
                if (class_exists($class)) {
                    $this->controller = Str::snake($class_name);
                    $methods = [];
                    $reflectionClass = new \ReflectionClass($class);
                    //通过反射获取类的注释
                    $doc = $reflectionClass->getDocComment();
                    //解析类的注释头
                    $ParseDoc = new ParseDoc();
                    $paras_result = $ParseDoc->parse($doc);
                    $class_docs = $paras_result;
                    $method_docs = [];
                    foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                        //输出测试
                        if (! $this->isMagicMethod($method->getName()) && $method->isPublic() && $method->getName() !== 'initialize' && $method->getName() !== 'validate') {
                            $method_doc = $method->getDocComment();
                            //解析注释
                            $info = $ParseDoc->parse($method_doc);
                            $method_docs += [$method->getName() => $info];
                            //获取方法的参数
                            $params = $method->getParameters();
                            foreach ($params as $param) {
                                //参数是否设置了默认参数，如果设置了，则获取其默认值
                                $arguments[$param->getName()] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                            }
                            $methods[] = Str::snake($method->getName());
                        }
                    }
                    $class_docs['method'] = $method_docs;
                    $class_docs['class'] = $class_name;
                    $class_docs['namespace'] = $reflectionClass->getNamespaceName();
                    $class_list[] = $class_docs;
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
                // dd($key);
                break;
            }
        }
        if (is_null($this->namespace)) {
            foreach ($packagesPsr4 as $key => $_namespace) {
                if ($namespace == $key) {
                    $this->namespace = $key;
                    dd($key);

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
