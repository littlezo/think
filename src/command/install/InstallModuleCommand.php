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

use littler\library\InstallModule;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class InstallModuleCommand extends Command
{
    protected function configure()
    {
        $this->setName('lz-install:module')
            ->addArgument('module', Argument::REQUIRED, 'module name')
            ->addOption('app', '-app', Option::VALUE_NONE, 'module install at [app] path')
            ->setDescription('install little module');
    }

    protected function execute(Input $input, Output $output)
    {
        $module = $input->getArgument('module');

        $install = (new InstallModule())->setModule($module)
            ->setInstallPath($input->getOption('app'));

        $output->info('start download module ' . $module);

        if (! $install->download()) {
            exit($output->error("install module [{$module}] failed"));
        }

        $install->install();

        $output->info("install module [ {$module} ] successfully");
    }
}
