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

use littler\generate\BatchGenerator;
use littler\library\Composer;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class AutoGeneratorCommand extends Command
{
	public const RELY_PACKAGE = 'nikic/php-parser';

	protected function configure()
	{
		$this->setName('lz-auto:batch')
		->addArgument('namespace', Argument::REQUIRED, '根命名空间')
		->addArgument('layer', Argument::REQUIRED, '应用层')
		->setDescription('批量生成控制器和模型')
			->setHelp(
				<<<'EOT'
					执行批量生成 <info>lz-auto:batch namespace layer</info>
					<info>namespace</info> 所在根命名空间
					<info>layer</info> 所在应用层 如： admin api
					实例 <info>lz-auto:batch little admin</info>
					EOT
			);
	}

	protected function execute(Input $input, Output $output)
	{
		$namespace = $input->getArgument('namespace');
		$layer = $input->getArgument('layer');
		$auth =   $output->ask($input, '请输入 jwt 授权应用 默认 default') ?: 'default';
		// $output->ask($input, ' false') ?: 'y';

		$asn =  $this->output->ask($this->input, '是否获取页面布局(Y/N) 默认 N') ?? 'N';

		$is_layout = false;
		if (strtolower($asn) == 'y') {
			$is_layout = true;
		}
		// 判断是否安装了扩展包
		if (! (new Composer())->hasPackage(self::RELY_PACKAGE)) {
			$output->error(sprintf('you must use [ composer require --dev %s]', self::RELY_PACKAGE));
			exit(0);
		}
		$message = (new BatchGenerator())->done($namespace, $layer, $auth, $is_layout);
		// dd($message);
		$output->info($message);
		$output->info('success');
	}
}
