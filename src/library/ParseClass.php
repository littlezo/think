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
use think\facade\Cache;
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
     * 获取根命名空间.
     * @param $module
     * @param mixed $path
     */
    public static function getRootNamespace($path): ?string
    {
        $composer = new Composer();
        $psr4Autoload = $composer->psr4Autoload();
        $packagesPsr4Autoload = $composer->packagesPsr4Autoload();
        $psr4 = array_merge($psr4Autoload, $packagesPsr4Autoload);
        $namespace = null;
        foreach ($psr4 as $_namespace => $item) {
            foreach ($item as $_path) {
                if (! is_bool(stripos($path, $_path))) {
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
        $class_list = Cache::get('class_list_' . $layer);
        if ($class_list) {
            return $class_list;
        }
        $class_file = FileSystem::allFiles(root_path());
        $class_list = [];
        foreach ($class_file as $item) {
            $path = $item->getPath();
            $relative_path = str_replace(root_path(), '', $path);
            $pos = stripos($relative_path, $layer);
            $file_name = $item->getFilename();
            if (! $pos) {
                continue;
            }
            if ($item->getExtension() !== 'php') {
                continue;
            }
            $namespace = $this->getRootNamespace($relative_path) ?? false;
            if (! $namespace) {
                continue;
            }
            $module_namespace = substr(str_replace(['/', '\\\\'], '\\', $relative_path), (int) strpos(str_replace(['/', '\\\\'], '\\', $relative_path), $namespace));
            $is_package = stripos($relative_path, 'src');
            if ($is_package) {
                $module_namespace = $namespace . substr(str_replace(['/', '\\\\'], '\\', $relative_path), $is_package + 4);
            }
            $is_test = stripos($relative_path, 'test');
            $is_tests = stripos($relative_path, 'tests');
            if ($is_test || $is_tests) {
                continue;
            }
            if (stripos($relative_path, 'laravel')) {
                continue;
            }
            $class_name = str_replace('.php', '', $file_name);
            $class = $module_namespace . '\\' . $class_name;
            try {
                if (class_exists($class)) {
                    $class_list[] = [
                        'class' => $class,
                        'path' => $relative_path . DIRECTORY_SEPARATOR . $file_name,
                    ];
                }
            } catch (\Throwable $t) {
                continue;
            }
        }
        Cache::tag('class_list')->set('class_list_' . $layer, $class_list);
        return $class_list;
    }

    /**
     * 获取所有路由.
     *
     * @param string $layer 层名 admin api shop ...
     * @throws \ReflectionException
     * @return \ReflectionClass
     */
    public function getRoutes($layer = null)
    {
        $route_list = Cache::get('route_list_' . $layer ?? 'all');
        if ($route_list) {
            return $route_list;
        }
        $class_list = $this->getAllClass();
        $route_list = [];
        foreach ($class_list as $item) {
            $class = $item['class'];
            if ($layer !== 'all' && is_bool(stripos($class, $layer))) {
                continue;
            }
            if (class_exists($class)) {
                $methods = [];
                $reflectionClass = new \ReflectionClass($class);
                //通过反射获取类的注释
                $doc = $reflectionClass->getDocComment();
                //解析类的注释头
                $ParseDoc = new ParseDoc();
                $paras_result = $ParseDoc->parse($doc);
                if (! $paras_result || ! $paras_result['group'] ?? false || ! $paras_result['resource'] ?? false) {
                    continue;
                }
                $class_docs = [
                    'title' => $paras_result['title'] ?? '',
                    'group' => $paras_result['group'] ?? '',
                    'resource' => str_replace('_', '/', $paras_result['resource']) ?? '',
                    'version' => $paras_result['version'] ?? '',
                    'auth' => $paras_result['auth'] ?? '',
                ];
                $method_docs = [];
                foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    //输出测试
                    if (! $this->isMagicMethod($method->getName()) && $method->isPublic() && $method->getName() !== 'initialize' && $method->getName() !== 'validate') {
                        $method_doc = $method->getDocComment();
                        //解析注释
                        $info = (new ParseDoc())->parse($method_doc);
                        $route = [
                            'title' => $info['title'] ?? '',
                            'group' => $class_docs['group'] ?? '',
                            'resource' => $class_docs['resource'] ?? '',
                            'auth' => $class_docs['auth'] ?? '',
                            'version' => $info['version'] ?? '',
                            'param' => $info['param'] ?? [],
                            'route' => $info['route'] ?? '',
                            'is_allow' => isset($info['is_allow']) ?? false,
                            'info' => $info,
                        ];
                        if (! $route['route']) {
                            continue;
                        }
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
                $class_docs['method'] = $method_docs;
                $class_docs['class'] = $class;
                $route_list[] = $class_docs;
            }
        }
        Cache::tag('routes_list')->set('route_list_' . $layer, $route_list);
        return $route_list;
    }

    /**
     * 获取模块路由.
     *
     * @param string $module 模块 user goods ...
     * @param string $layer 层名 admin api ...
     * @throws \ReflectionException
     * @return \ReflectionClass
     */
    public function getModuleRoutes($layer = null)
    {
        $route_list = Cache::get('route_module_list_' . $this->module . '_' . $layer);
        if ($route_list) {
            return $route_list;
        }
        $class_list = $this->getAllClass();
        $route_list = [];
        foreach ($class_list as $item) {
            $class = $item['class'];
            if ($layer !== 'all' && is_bool(stripos($class, $layer))) {
                continue;
            }
            $class_module = substr($class, 0, strrpos($class, 'controller'));
            if ($this->module && is_bool(stripos($class_module, $this->module))) {
                continue;
            }
            if (class_exists($class)) {
                $methods = [];
                $reflectionClass = new \ReflectionClass($class);
                //通过反射获取类的注释
                $doc = $reflectionClass->getDocComment();
                //解析类的注释头
                $ParseDoc = new ParseDoc();
                $paras_result = $ParseDoc->parse($doc);
                if (! $paras_result || ! $paras_result['group'] ?? false || ! $paras_result['resource'] ?? false) {
                    continue;
                }

                $class_docs = [
                    'title' => $paras_result['title'] ?? '',
                    'group' => $paras_result['group'] ?? '',
                    'resource' => str_replace('_', '/', $paras_result['resource']) ?? '',
                    'version' => $paras_result['version'] ?? '',
                ];
                $method_docs = [];
                foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    //输出测试
                    if (! $this->isMagicMethod($method->getName()) && $method->isPublic() && $method->getName() !== 'initialize' && $method->getName() !== 'validate') {
                        $method_doc = $method->getDocComment();
                        //解析注释
                        $ParseDoc = new ParseDoc();
                        $info = $ParseDoc->parse($method_doc);
                        $route = [
                            'title' => $info['title'] ?? '',
                            'group' => $class_docs['group'] ?? '',
                            'resource' => $class_docs['resource'] ?? '',
                            'version' => $info['version'] ?? '',
                            'param' => $info['param'] ?? [],
                            'route' => $info['route'] ?? '',
                        ];
                        if (! $route['route']) {
                            continue;
                        }
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
                $class_docs['method'] = $method_docs;
                $class_docs['class'] = $class;
                $route_list[] = $class_docs;
            }
        }
        Cache::tag('routes_list')->set('route_module_list_' . $this->module . '_' . $layer, $route_list);
        return $route_list;
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
