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
namespace littler\generate\factory;

use littler\facade\FileSystem;
use littler\generate\template\Content;
use think\helper\Str;

class Route extends Factory
{
    use Content;

    protected $controllerName;

    protected $controller;

    protected $restful;

    protected $layer;

    protected $methods = [];

    protected $stubDir;

    public function init()
    {
        $this->stubDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR .
            'command' . DIRECTORY_SEPARATOR .
            'stubs' . DIRECTORY_SEPARATOR;
    }

    public function done(array $params = [])
    {
        $this->init();
        $route = [];

        if (! $params['extra']['not_route'] ?? false) {
            if ($this->layer !== 'api') {
                $route[] = sprintf("\$router->group('api/%s', function () use (\$router) {", $this->layer);
            } else {
                $route[] = sprintf("\$router->group('api', function () use (\$router) {");
            }
            if ($this->restful) {
                $route[] = '// ' . $this->layer . ' ' . $this->controllerName . '路由';
                $route[] = sprintf("\$router->resource('%s', '\\%s');", $this->controllerName, $this->controller);
            }
            if (! empty($this->methods)) {
                foreach ($this->methods as $method) {
                    $route[] = sprintf("\$router->%s('%s/%s', '\\%s@%s');", $method[1], $this->controllerName, $method[0], $this->controller, $method[0]);
                }
            }
            // dd($route);
            $router = $this->getModulePath($this->controller) . 'route.php';
            $comment = '// ' . $this->layer . ' ' . '路由';
            array_unshift($route, $comment);
            // dd($this->parseRoute($router, $route));

            if (! file_exists($router)) {
                FileSystem::put($router, FileSystem::sharedGet($this->stubDir . 'route.stub'));
            }
            return FileSystem::put($router, $this->parseRoute($router, $route));
            // return FileSystem::put($router, $this->header() . $comment . implode(';' . PHP_EOL, $route) . ';');
        }
    }

    /**
     * set class.
     *
     * @param $class
     * @return $this
     */
    public function controller($class)
    {
        $this->controller = $class;

        $class = explode('\\', $class);

        $this->controllerName = Str::snake(array_pop($class));

        return $this;
    }

    /**
     * set restful.
     *
     * @param $restful
     * @return $this
     */
    public function restful($restful)
    {
        $this->restful = $restful;

        return $this;
    }

    public function layer($layer)
    {
        $this->layer = $layer;
        return $this;
    }

    /**
     * set methods.
     *
     * @param $methods
     * @return $this
     */
    public function methods($methods)
    {
        $this->methods = $methods;

        return $this;
    }

    protected function parseRoute($path, $route)
    {
        $file = new \SplFileObject($path);
        // 保留所有行
        $lines = [];
        // 结尾之后的数据
        $down = [];
        // 结尾数据
        $end = '';
        while (! $file->eof()) {
            $lines[] = rtrim($file->current(), PHP_EOL);
            $file->next();
        }

        while (count($lines)) {
            $line = array_pop($lines);
            if (strpos($line, '})') !== false) {
                $end = $line;
                break;
            }
            array_unshift($down, $line);
        }
        $is_new = true;
        foreach ($lines as $key => $value) {
            if (trim($value) === trim($route[1])) {
                array_splice($lines, $key + 1, 0, array_slice($route, 2)); // 插入到位置3且删除0个
                $is_new = false;
            }
        }

        if ($is_new) {
            array_push($route, $end);
            $lines = array_merge($lines, $route);
        }
        $router = implode(PHP_EOL, $lines) . PHP_EOL;
        return $router .= $end;
    }
}
