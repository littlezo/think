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
namespace littler\command;

use littler\App;
use littler\generate\factory\Controller;
use littler\library\Composer;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class ControllerGeneratorCommand extends Command
{
    const RELY_PACKAGE = 'nikic/php-parser';

    protected function configure()
    {
        $this->setName('lz-create:controller')
            ->setDescription('create controller');
    }

    protected function execute(Input $input, Output $output)
    {
        // 判断是否安装了扩展包
        if (! (new Composer())->hasPackage(self::RELY_PACKAGE)) {
            $output->error(sprintf('you must use [ composer require --dev %s]', self::RELY_PACKAGE));
            exit(0);
        }
        $module = $output->ask($input, '请输入模块');
        if (! $module) {
            $output->error('请输入模块名');
            exit(0);
        }
        $module = strtolower($module);
        $layer = strtolower($output->ask($input, '请输入控制器层级 默认admin') ?? 'admin');
        $controller = $output->ask($input, '请输入控制器名');
        if (! $controller) {
            $output->error('请输入控制器名');
            exit(0);
        }
        $controller = ucfirst($controller);
        $model = $output->ask($input, '请输入模型名') ?? '';
        if ($model) {
            // $output->error('请输入模型名');
            // exit(0);
            $model = ucfirst($model);
        }
        $params = [
            'controller' => 'little\\' . $module . '\\' . $layer . '\\controller\\' . $controller,
            'controller_repository' => 'little\\' . $module . '\\repository\\' . $layer . '\\' . $controller . 'Traits',
            'model' => 'little\\' . $module . '\\model\\' . $model,
            'extra' => [
            ],
        ];
        if (! class_exists($params['model'])) {
            $output->error(sprintf('模型 %s 不存在！', $params['model'] . '::class'));
            exit(0);
        }
        $controllerFile = App::getModuleDirectory($module, $layer) . 'controller' . DIRECTORY_SEPARATOR . $model . '.php';
        $repositoryFile = App::getModuleDirectory($module, 'repository') . $layer . DIRECTORY_SEPARATOR . $controller . 'Traits' . '.php';

        (new Controller())->done($params);
        if (file_exists($controllerFile)) {
            $output->info(sprintf('Controller %s Create Successfully!', $controllerFile));
        } else {
            $output->error(sprintf('Controller %s Create Failed!', $controllerFile));
        }
        if (file_exists($repositoryFile)) {
            $output->info(sprintf('Traits %s Create Successfully!', $repositoryFile));
        } else {
            $output->error(sprintf('Traits %s Create Failed!', $repositoryFile));
        }
    }
}
