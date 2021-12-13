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

use littler\App;
use littler\facade\FileSystem;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class CreateTableCommand extends Command
{
	protected $table;

	protected function configure()
	{
		// 指令配置
		$this->setName('create:table')
			->addArgument('module', Argument::REQUIRED, 'module name')
			->addArgument('table', Argument::REQUIRED, 'table name')
			->addOption('form', '-f', Option::VALUE_NONE, '是否需要 form')
			->setDescription('create table ');
	}

	protected function execute(Input $input, Output $output)
	{
		$module = $input->getArgument('module');
		$table = $input->getArgument('table');

		$form = $input->getOption('form');

		FileSystem::put(
			App::moduleDirectory($module) . 'tables' . DIRECTORY_SEPARATOR . (ucwords($table) . '.php'),
			$this->tableTemp($module, ucwords($table), $form)
		);

		if (! $form) {
			FileSystem::put(
				App::moduleDirectory($module) .
				'tables' . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR
				. (ucwords($table) . '.php'),
				$this->formTemp($module, ucwords($table))
			);
		}

		$output->info('created success~');
	}

	protected function tableTemp($module, $table, $form)
	{
		$_table = lcfirst($table);

		$formTemp = ! $form ? sprintf('Factory::create(\'%s\');', $_table) : '[];';

		return <<<PHP
			<?php
			namespace little\\{$module}\\tables;

			use littler\\Table;
			use little\\{$module}\\tables\\forms\\Factory;

			class {$table} extends Table
			{
			    public function table()
			    {
			        // TODO: Implement table() method.
			       return \$this->getTable('{$_table}');
			    }

			    protected function form()
			    {
			        // TODO: Implement form() method.
			        return {$formTemp}
			    }

			}
			PHP;
	}

	protected function formTemp($module, $table)
	{
		return <<<PHP
			<?php
			namespace little\\{$module}\\tables\\forms;

			use littler\\library\\form\\Form;

			class {$table} extends Form
			{
			    public function fields(): array
			    {
			        // TODO: Implement fields() method.
			        return [

			        ];
			    }
			}
			PHP;
	}
}
