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
use Phinx\Migration\MigrationInterface;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option as InputOption;
use think\console\Output;
use think\migration\command\migrate\Rollback;

class MigrateRollbackCommand extends Rollback
{
	protected $module;

	protected function configure()
	{
		$this->setName('lz-migrate:rollback')
			->setDescription('Rollback the last or to a specific migration')
			->addArgument('module', Argument::REQUIRED, 'migrate the module database')
			->addOption('--target', '-t', InputOption::VALUE_REQUIRED, 'The version number to rollback to')
			->addOption('--date', '-d', InputOption::VALUE_REQUIRED, 'The date to rollback to')
			->addOption('--force', '-f', InputOption::VALUE_NONE, 'Force rollback to ignore breakpoints')
			->setHelp(
				<<<'EOT'
					The <info>lz-migrate:rollback</info> command reverts the last migration, or optionally up to a specific version

					<info>php think lz-migrate:rollback</info>
					<info>php think lz-migrate:rollback module -t 20111018185412</info>
					<info>php think lz-migrate:rollback module -d 20111018</info>
					<info>php think lz-migrate:rollback -v</info>

					EOT
			);
	}

	/**
	 * Rollback the migration.
	 */
	protected function execute(Input $input, Output $output)
	{
		$this->module = $input->getArgument('module');
		$version = $input->getOption('target');
		$date = $input->getOption('date');
		$force = (bool) $input->getOption('force');

		// rollback the specified environment
		$start = microtime(true);
		if ($date !== null) {
			$this->rollbackToDateTime(new \DateTime($date), $force);
		} else {
			if (! $version) {
				$migrations = glob(App::moduleMigrationsDirectory($this->module) . '*.php');
				foreach ($migrations as $migration) {
					$version = explode('_', basename($migration))[0];
					$this->rollback($version, $force);
				}
			} else {
				$this->rollback($version, $force);
			}
		}
		$end = microtime(true);
		$this->migrations = null;
		$output->writeln('');
		$output->writeln('<comment>All Done. Took ' . sprintf('%.4fs', $end - $start) . '</comment>');
	}

	/**
	 * 获取 migration path.
	 */
	protected function getPath(): string
	{
		return App::moduleMigrationsDirectory($this->module);
	}

	/**
	 * @param null $version
	 * @param bool $force
	 */
	protected function rollback($version = null, $force = false)
	{
		$migrations = $this->getMigrations();
		$versionLog = $this->getVersionLog();
		$versions = array_keys($versionLog);

		if ($version) {
			$this->executeMigration($migrations[$version], MigrationInterface::DOWN);
		} else {
			foreach ($migrations as $key => $migration) {
				if (in_array($key, $versions, true)) {
					$this->executeMigration($migration, MigrationInterface::DOWN);
				}
			}
		}
	}
}
