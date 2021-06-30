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

use littler\App;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option as InputOption;
use think\console\Output;
use think\migration\command\seed\Run;

class SeedRunCommand extends Run
{
	protected $module;

	protected function configure()
	{
		// 指令配置
		$this->setName('lz-seed:run')
			->setDescription('the lz-seed:run command to Run database seeders')
			->addArgument('module', Argument::REQUIRED, 'seed the module database')
			->addOption('--seed', '-s', InputOption::VALUE_REQUIRED, 'What is the name of the seeder?')
			->setHelp(
				<<<'EOT'
					                The <info>lz-seed:run</info> command runs all available or individual seeders
					<info>php think lz-seed:run module</info>
					<info>php think lz-seed:run -s UserSeeder</info>
					<info>php think lz-seed:run -v</info>

					EOT
			);
	}

	protected function execute(Input $input, Output $output)
	{
		$this->module = strtolower($input->getArgument('module'));
		$seed = $input->getOption('seed');

		// run the seed(ers)
		$start = microtime(true);
		$this->seed($seed);
		$end = microtime(true);
		$this->seeds = null;
		$output->writeln('<comment>All Done. Took ' . sprintf('%.4fs', $end - $start) . '</comment>');
	}

	/**
	 * 获取 seeder path.
	 * @param $module
	 * @return string
	 * @date: 2019/12/10 14:01
	 */
	protected function getPath()
	{
		return App::moduleSeedsDirectory($this->module);
	}
}
