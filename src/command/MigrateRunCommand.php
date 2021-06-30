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
use think\migration\command\migrate\Run;

class MigrateRunCommand extends Run
{
	protected $module;

	public function configure()
	{
		$this->setName('lz-migrate:run')
			->setDescription('Migrate the database')
			->addArgument('module', Argument::REQUIRED, 'migrate the module database')
			->addOption('--target', '-t', InputOption::VALUE_REQUIRED, 'The version number to migrate to')
			->addOption('--date', '-d', InputOption::VALUE_REQUIRED, 'The date to migrate to')
			->setHelp(
				<<<'EOT'
					The <info>migrate:run</info> command runs all available migrations, optionally up to a specific version

					<info>php think lz-migrate:run module</info>
					<info>php think lz-migrate:run module -t 20110103081132</info>
					<info>php think lz-migrate:run module -d 20110103</info>
					<info>php think lz-migrate:run -v</info>

					EOT
			);
	}

	protected function execute(Input $input, Output $output)
	{
		$this->module = strtolower($input->getArgument('module'));
		$version = $input->getOption('target');
		$date = $input->getOption('date');

		// run the migrations
		$start = microtime(true);
		if ($date !== null) {
			$this->migrateToDateTime(new \DateTime($date));
		} else {
			$this->migrate($version);
		}
		$end = microtime(true);

		// 重置 migrations 在循环冲无法重复使用
		$this->migrations = null;
		$output->writeln('');
		$output->writeln('<comment>All Done. Took ' . sprintf('%.4fs', $end - $start) . '</comment>');
	}

	/**
	 * 获取 migration path.
	 *
	 * @param $module
	 * @return string
	 */
	protected function getPath()
	{
		return App::moduleMigrationsDirectory($this->module);
	}
}
