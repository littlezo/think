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
namespace littler\command\Tools;

use little\permissions\model\Permissions;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class MakeMenuCommand extends Command
{
    protected $table;

    protected function configure()
    {
        // 指令配置
        $this->setName('make:menu')
            ->addArgument('controller', Argument::REQUIRED, '完整的控制器名称,eg. little\\permissions\\controller\\User')
            ->addArgument('menu', Argument::REQUIRED, '菜单名称')
            ->addArgument('path', Argument::REQUIRED, '前端路由地址')
            ->addArgument('component', Argument::REQUIRED, '前端组件名称')
            ->setDescription(
                <<<'DES'
                    controller: 完整的控制器名称,eg:little\permissions\controller\User
                    menu: 菜单名称
                    path: 前端路由地址
                    component: 前端组件名称
                    DES
            );
    }

    protected function execute(Input $input, Output $output)
    {
        $arguments = $input->getArguments();

        try {
            [$root, $module, $c, $controller] = explode('\\', $arguments['controller']);

            $permission = Permissions::where('module', $module)
                ->where('parent', 0)->find();

            $permissionModel = $this->app->make(Permissions::class);

            // 菜单是否已经建立
            $hasMenu = Permissions::where('module', $module)
                ->where('mark', lcfirst($controller))->find();
            if (! $hasMenu) {
                $id = $permissionModel->createBy([
                    'name' => $arguments['menu'],
                    'module' => $module,
                    'parent' => $permission->id,
                    'level' => $permission->id,
                    'path' => $arguments['path'],
                    'method' => 'get',
                    'mark' => lcfirst($controller),
                    'component' => $arguments['component'],
                ]);
            } else {
                $id = $hasMenu->id;
            }

            $reflectClass = new \ReflectionClass($this->app->make($arguments['controller']));

            $exceptMethods = $this->getExceptionMethods($reflectClass);

            $methods = $this->getCurrentControllerMethods($reflectClass);

            $initMethods = $this->initMethods();

            foreach ($methods as $method) {
                if (! in_array($method, $exceptMethods)) {
                    $hasInit = $initMethods[$method] ?? false;
                    // 如果已经存在 直接跳过
                    if (Permissions::where('module', $module)
                        ->where('mark', lcfirst($controller) . '@' . $method)->find()) {
                        continue;
                    }
                    $data = [
                        'level' => $permission->id . '-' . $id,
                        'mark' => lcfirst($controller) . '@' . $method,
                        'parent' => $id,
                        'module' => $module,
                        'type' => Permissions::BTN_TYPE,
                    ];
                    if (! $hasInit) {
                        $name = $output->ask($input, sprintf('请输入方法【%s】的菜单名称', $method));
                        $data['name'] = $name;
                    } else {
                        [$name, $httpMethod] = $initMethods[$method];
                        $data['name'] = $name;
                        $data['method'] = $httpMethod;
                    }

                    $permissionModel->createBy($data);
                }
            }

            $output->info('success');
        } catch (\Exception $e) {
            $output->error($e->getMessage());
        }
        //dd($reflectClass->getMethods());
       // dd($this->app->make($arguments['controller'])->methods());
    }

    /**
     * 获取 except 方法.
     *
     * @return array
     */
    protected function getExceptionMethods(\ReflectionClass $class)
    {
        $methods = [];

        $methods[] = '__construct';

        foreach ($class->getParentClass()->getMethods() as $method) {
            $methods[] = $method->getName();
        }

        return $methods;
    }

    /**
     * 获取当前控制器的方法.
     *
     * @return array
     */
    protected function getCurrentControllerMethods(\ReflectionClass $class)
    {
        $methods = [];

        foreach ($class->getMethods() as $method) {
            $methods[] = $method->getName();
        }

        return $methods;
    }

    /**
     * 初始化方法.
     *
     * @return \string[][]
     */
    protected function initMethods()
    {
        return [
            'index' => ['列表', 'get'],
            'save' => ['保存', 'post'],
            'read' => ['读取', 'get'],
            'update' => ['更新', 'put'],
            'delete' => ['删除', 'delete'],
        ];
    }
}
