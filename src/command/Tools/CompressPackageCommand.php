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

namespace littler\command\Tools;

use littler\library\Compress;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

/**
 * 打包模块.
 *
 * Class CompressPackageCommand
 */
class CompressPackageCommand extends Command
{
	protected function configure()
	{
		// 指令配置
		$this->setName('lz:unpack ')
			->addArgument('module', Argument::REQUIRED, 'module name')
			->setDescription('compress module to zip');
	}

	protected function execute(Input $input, Output $output)
	{
		$package = $this->input->getArgument('module');

		try {
			(new Compress())->moduleToZip($package);
		} catch (\Exception $e) {
			exit($output->error($e->getMessage()));
		}

		$output->info($package . ' zip successfully~');
	}
}
