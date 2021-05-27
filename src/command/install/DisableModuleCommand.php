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
namespace littler\command\install;

use littler\App;
use littler\library\InstallLocalModule;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class DisableModuleCommand extends Command
{
    protected function configure()
    {
        $this->setName('disable:module')
            ->addArgument('module', Argument::REQUIRED, 'module name')
            ->setDescription('disable little module');
    }

    protected function execute(Input $input, Output $output)
    {
        $module = $input->getArgument('module');

        if (empty(App::getModuleInfo(App::directory() . $module))) {
            $output->error("module [{$module}] not exist");
        } else {
            (new InstallLocalModule($module))->disableModule();
            $output->info("module [{$module}] disabled");
        }
    }
}
