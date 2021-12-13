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

namespace littler\command;

use littler\generate\factory\Module;
use littler\library\Composer;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class ModuleCreateCommand extends Command
{
	public const RELY_PACKAGE = 'nikic/php-parser';

	protected function configure()
	{
		$this->setName('lz-create:module')
			->addArgument('module', Argument::REQUIRED, 'module name')
			->setDescription('create module service');
	}

	protected function execute(Input $input, Output $output)
	{
		// 判断是否安装了扩展包
		if (! (new Composer())->hasPackage(self::RELY_PACKAGE)) {
			$output->error(sprintf('you must use [ composer require --dev %s]', self::RELY_PACKAGE));
			exit(0);
		}
		try {
			$param = [];
			$param['module'] = strtolower($input->getArgument('module'));
			$param['name'] = $output->ask($input, '请输入模块中文名称');
			if (! $param['name']) {
				while (true) {
					$param['name'] = $output->ask($input, '请输入模块中文名称');
					if ($param['name']) {
						break;
					}
				}
			}
			$param['description'] = $output->ask($input, '请输入模块描述') ?? $param['name'];
			// $param['description'] = $this->description ?: '';
			// dd($param);
			(new Module())->done($param);
		} catch (\Exception $exception) {
			$output->error($exception->getTraceAsString());
			exit;
		}

		$output->info('module created');
	}
}
