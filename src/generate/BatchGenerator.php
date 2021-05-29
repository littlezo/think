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
namespace littler\generate;

use littler\App;
use littler\exceptions\FailedException;
use littler\generate\factory\Controller;
use littler\generate\factory\Model;
use littler\generate\factory\Module;
use littler\generate\factory\Route;
use littler\library\Composer;
use littler\Utils;
use think\facade\Db;
use think\helper\Str;

class BatchGenerator
{
    const NEED_PACKAGE = 'nikic/php-parser';

    /**
     * generate.
     *
     * @param $params
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DataNotFoundException
     */
    public function done()
    {
        $message = [];
        // 判断是否安装了扩展包
        if (! (new Composer())->hasPackage(self::NEED_PACKAGE)) {
            throw new FailedException(
                sprintf('you must use [ composer require --dev %s]', self::NEED_PACKAGE)
            );
        }
        $tables = Db::getTables();
        $tables_array = [];
        foreach ($tables as $item) {
            $table = Utils::tableWithoutPrefix($item);
            $table_info = explode('_', $table);
            $module = explode('_', $table)[0];
            // dd($module);
            if (in_array('reserve', $table_info)) {
                continue;
            }
            if (in_array('migrations', $table_info)) {
                continue;
            }
            $module_exists = App::getModuleInfo($module)['name'] ?? false;
            if (! $module_exists) {
                (new Module())->done(['module' => $module]);
            }
            $tables_array[] = $table;
            $name = Str::studly($table);
            $params = [
                'model' => 'little\\' . $module . '\\model\\' . $name,
                'model_repository' => 'little\\' . $module . '\\repository\\model\\' . $name . 'Abstract',
                'controller' => 'little\\' . $module . '\\admin\\controller\\' . $name,
                'controller_repository' => 'little\\' . $module . '\\repository\\admin\\' . $name . 'Traits',
                'table' => Str::snake($name),
                'extra' => [
                    'soft_delete' => true,
                    'not_route' => false,
                ],
            ];
            $message[] = $this->execute($module, $name, $params);
        }
        return $message;
    }

    protected function execute($module, $name, $params)
    {
        $message = [];

        $files = [];
        try {
            if ($params['controller']) {
                $controllerFile = App::getModuleDirectory($module, 'admin') . 'controller' . DIRECTORY_SEPARATOR . $name . '.php';
                $repositoryControllerFile = App::getModuleDirectory($module, 'repository') . 'admin' . DIRECTORY_SEPARATOR . $name . 'Traits' . '.php';
                $files[] = (new Controller())->done($params);
                array_push($message, 'controller created successfully');
            }
            if ($params['model']) {
                $modelFile = App::getModuleModelDirectory($module) . $name . '.php';
                $modelAbstractFile = App::getModuleDirectory($module, 'repository/model') . $name . 'Abstract' . '.php';
                $files[] = (new Model())->done($params);
            }
            if ($params['controller']) {
                (new Route())->controller($params['controller'])
                    ->restful(true)
                    ->layer('admin')
                    ->done($params);
            }
        } catch (\Throwable $exception) {
            // dd($params);
            throw new FailedException((string) $exception->getMessage());
        }

        return $message;
    }
}
