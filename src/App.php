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
namespace littler;

use littler\facade\FileSystem;
use littler\library\ParseClass;

class App
{
    public const VERSION = '1.0.0';

    public static $root = 'little';

    public static function directory(): string
    {
        return app()->getRootPath() . self::$root . DIRECTORY_SEPARATOR;
    }

    /**
     * 设置 root.
     *
     * @param $root
     */
    public static function setRoot($root): App
    {
        self::$root = $root;

        return new self();
    }

    /**
     * 创建目录.
     */
    public static function makeDirectory(string $directory): string
    {
        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }

        return $directory;
    }

    /**
     * @param $module
     */
    public static function moduleDirectory($module): string
    {
        return self::makeDirectory(self::directory() . $module . DIRECTORY_SEPARATOR);
    }

    public static function cacheDirectory(): string
    {
        return self::makeDirectory(app()->getRuntimePath() . self::$root . DIRECTORY_SEPARATOR);
    }

    /**
     * 备份地址
     */
    public static function backupDirectory(): string
    {
        return self::makeDirectory(self::cacheDirectory() . 'backup' . DIRECTORY_SEPARATOR);
    }

    /**
     * @param $module
     */
    public static function moduleMigrationsDirectory($module): string
    {
        return self::directory() . $module . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR;
    }

    /**
     * @param $module
     */
    public static function moduleSeedsDirectory($module): string
    {
        $seedPath = self::directory() . $module . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeds' . DIRECTORY_SEPARATOR;

        self::makeDirectory($seedPath);

        return $seedPath;
    }

    /**
     * @param $module
     */
    public static function getModuleModelDirectory($module): string
    {
        return self::makeDirectory(self::directory() . $module . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR);
    }

    /**
     * @param $module
     * @param mixed $layer
     */
    public static function getModuleDirectory($module, $layer = 'model'): string
    {
        return self::makeDirectory(self::directory() . $module . DIRECTORY_SEPARATOR . $layer . DIRECTORY_SEPARATOR);
    }

    /**
     * @param $module
     */
    public static function getPackageModuleDirectory($module): string
    {
        $path = include app()->getRootPath() . ('vendor/composer/autoload_psr4.php');
        $services = [];
        $system = $path['little\\'];
        $system_modules = [];
        foreach ($system as $item) {
            $system_modules = array_merge($system_modules, glob($item . '/*'));
        }

        $user_modules = glob(self::directory() . '*');
        $modules = array_merge($system_modules, $user_modules);
        $__module = '';
        foreach ($modules as $key => $_module) {
            if (is_dir($_module)) {
                $pos = strrpos($_module, $module);
                if ($pos) {
                    $__module = $_module;
                }
            }
        }
        return $__module;
    }

    public static function getModulesDirectory(): array
    {
        $path = include app()->getRootPath() . ('vendor/composer/autoload_psr4.php');
        $services = [];
        $system = $path['little\\'];
        $system_modules = [];
        foreach ($system as $item) {
            $system_modules = array_merge($system_modules, glob($item . '/*'));
        }
        $user_modules = glob(self::directory() . '*');
        $modules = array_merge($system_modules, $user_modules);
        foreach ($modules as $key => &$module) {
            if (! is_dir($module)) {
                unset($modules[$key]);
            }

            $module .= DIRECTORY_SEPARATOR;
        }

        return $modules;
    }

    /**
     * @param bool $select
     */
    public static function getModulesInfo($select = true): array
    {
        $modules = [];
        if ($select) {
            foreach (self::getModulesDirectory() as $module) {
                $moduleInfo = self::getModuleInfo($module);
                $modules[] = [
                    'value' => $moduleInfo['name'],
                    'title' => $moduleInfo['title'],
                ];
            }
        } else {
            foreach (self::getModulesDirectory() as $module) {
                $moduleInfo = self::getModuleInfo($module);
                $modules[$moduleInfo['name']] = $moduleInfo['title'];
            }
        }

        return $modules;
    }

    /**
     * 获取可用模块.
     */
    public static function getEnabledService(): array
    {
        $services = [];

        foreach (self::getModulesDirectory() as $module) {
            if (is_dir($module)) {
                $moduleInfo = self::getModuleInfo($module);
                // 如果没有设置 info.json 默认加载
                $moduleServices = $moduleInfo['services'] ?? [];
                if (! empty($moduleServices) && $moduleInfo['enable']) {
                    $services = array_merge($services, $moduleServices);
                }
            }
        }
        // dd($services);

        return $services;
    }

    /**
     * 获取模块 Json.
     *
     * @param $module
     */
    public static function getModuleJson($module): string
    {
        if (is_dir($module)) {
            return $module . DIRECTORY_SEPARATOR . 'config/info.json';
        }

        return self::moduleDirectory($module) . 'config/info.json';
    }

    /**
     * 获取模块信息.
     *
     * @param $module
     */
    public static function getModuleInfo($module): array
    {
        $moduleJson = self::getModuleJson($module);

        if (! file_exists($moduleJson)) {
            return [];
        }

        return \json_decode((string) FileSystem::sharedGet($moduleJson), true);
    }

    /**
     * 更新模块信息.
     *
     * @param $module
     * @param $info
     */
    public static function updateModuleInfo($module, $info): bool
    {
        $moduleInfo = self::getModuleInfo($module);

        if (! count($moduleInfo)) {
            return false;
        }

        foreach ($moduleInfo as $k => $v) {
            if (isset($info[$k])) {
                $moduleInfo[$k] = $info[$k];
            }
        }

        if (! is_writeable(self::getModuleJson($module))) {
            chmod(self::getModuleJson($module), 666);
        }

        FileSystem::put(self::getModuleJson($module), \json_encode($moduleInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return true;
    }

    /**
     * 获取服务
     */
    public static function getServices(): array
    {
        if (file_exists(self::getCacheServicesFile())) {
            return self::getCacheServices();
        }

        return self::getModuleServices();
    }

    /**
     * @return mixed
     */
    public static function getRoutes(): array
    {
        if (file_exists(self::getCacheRoutesFile())) {
            return [self::getCacheRoutesFile()];
        }

        return self::getModuleRoutes();
    }

    public static function getModuleRouter()
    {
        $routeFiles = [];
        $parseClass = new ParseClass();
        $loadModule = app(App::class)->make('loadModule')->get();
        $router_list = [];
        $router = app(Route::class);
        foreach ($loadModule as $key => $item) {
            $namespace_string = str_replace(['/', '.', ':', '\\'], '.', $key);
            $namespace_array = explode('.', $namespace_string);
            $namespace = $namespace_array[0] ?? null;
            $module = $namespace_array[1] ?? null;
            if (! $module || ! $namespace) {
                break;
            }
            $router_list = array_merge(
                $router_list,
                $parseClass->setNamespace($namespace)
                    ->setPath($item)
                    ->setModule($module)
                    ->getClassMethod()
            );
        }
        $route_rule = [];
        foreach ($router_list as $_namespace => $route) {
            $rule = explode('\\', $_namespace);
            $scheme = $rule[0] ?? false;
            $module = $rule[1] ?? false;
            $controller = $rule[2] ?? false;
            if (! $scheme || ! $module || ! $controller) {
                break;
            }
            $route_rule = array_merge($route_rule, ["{$scheme}://{$module}/{$controller}" => $route]);
        }
        return $route_rule;
    }

    public static function getModuleRoutes(): array
    {
        $routeFiles = [];
        foreach (self::getModulesDirectory() as $module) {
            $moduleInfo = self::getModuleInfo($module);
            $moduleAlias = $moduleInfo['name'] ?? '';
            if (! in_array($moduleAlias, ['login']) && file_exists($module . 'route.php')) {
                $routeFiles[] = $module . 'route.php';
            }
        }

        return $routeFiles;
    }

    /**
     * @return false|int
     */
    public static function cacheRoutes()
    {
        $routes = '';

        foreach (self::getModuleRoutes() as $route) {
            $routes .= trim(str_replace('<?php', '', file_get_contents($route))) . PHP_EOL;
        }

        return file_put_contents(self::getCacheRoutesFile(), "<?php\r\n " . $routes);
    }

    /**
     * @return false|int
     */
    public static function cacheServices()
    {
        return file_put_contents(self::getCacheServicesFile(), "<?php\r\n return "
            . var_export(self::getEnabledService(), true) . ';');
    }

    /**
     * @return mixed
     */
    public static function getCacheServicesFile(): string
    {
        return self::cacheDirectory() . 'services.php';
    }

    protected static function getModuleServices(): array
    {
        $services = [];

        foreach (self::getModulesDirectory() as $module) {
            if (is_dir($module)) {
                $moduleInfo = self::getModuleInfo($module);
                if (isset($moduleInfo['services']) && ! empty($moduleInfo['services'])) {
                    $services = array_merge($services, $moduleInfo['services']);
                }
            }
        }

        return $services;
    }

    /**
     * @return mixed
     */
    protected static function getCacheServices()
    {
        return include self::getCacheServicesFile();
    }

    protected static function getCacheRoutesFile(): string
    {
        return self::cacheDirectory() . 'routes.php';
    }
}
