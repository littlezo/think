<?php

declare(strict_types=1);

/*
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。
 * ## 只要思想不滑稽，方法总比苦难多！
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @link     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 *
 */

namespace littler\command\install;

use littler\library\InstallLocalModule;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class InstallLocalModuleCommand extends Command
{
	protected function configure()
	{
		$this->setName('local:install')
			->addArgument('module', Argument::REQUIRED, 'module name')
			->setDescription('install little local module');
	}

	protected function execute(Input $input, Output $output)
	{
		$installedModule = $input->getArgument('module');

		$install = new InstallLocalModule($installedModule);

		if (! $install->localModuleExist()) {
			while (true) {
				$modules = $install->getLocalModulesInfo(false);
				if (! count($modules)) {
					$output->error('Input module not found and All local modules had been enabled');
					exit;
				}
				$choose = '';
				$i = 1;
				foreach ($modules as $k => $module) {
					$choose .= ($i++) . ':' . ($module['name']) . ($module['enable'] ? '(开启)' : '(未开启)') . PHP_EOL;
				}
				$answer = $output->ask($input, $choose);
				if (isset($modules[$answer - 1])) {
					$installedModule = $modules[$answer - 1]['name'];
					break;
				}
			}
		}

		$install = new InstallLocalModule($installedModule);

		if (! $install->isModuleEnabled()) {
			$output->error($installedModule . ' has been enabled!');
			exit;
		}

		if (! $install->done()) {
			$output->error(sprintf('module [%s] has been installed, You can use [php think enable:module $module] to start it.', $installedModule));
		}

		$output->info(sprintf('module [%s] installed successfully', $installedModule));
	}
}
