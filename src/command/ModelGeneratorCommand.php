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
use littler\generate\factory\Model;
use littler\library\Composer;
use littler\Utils;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;
use think\helper\Str;

class ModelGeneratorCommand extends Command
{
	public const RELY_PACKAGE = 'nikic/php-parser';

	protected function configure()
	{
		$this->setName('lz-create:model')
			->addArgument('module', Argument::REQUIRED, 'module name')
			->addArgument('model', Argument::REQUIRED, 'model name')
			->addOption('allSuffix', '-a', Option::VALUE_REQUIRED, 'create all suffix Table model')
			->addOption('softDelete', '-d', Option::VALUE_REQUIRED, 'soft delete')
			->setDescription('create model');
	}

	protected function execute(Input $input, Output $output)
	{
		// 判断是否安装了扩展包
		if (! (new Composer())->hasPackage(self::RELY_PACKAGE)) {
			$output->error(sprintf('you must use [ composer require --dev %s]', self::RELY_PACKAGE));
			exit(0);
		}
		$allSuffix = $input->getOption('allSuffix') ?? false;
		$input_model = ucfirst($input->getArgument('model'));
		$model = ucfirst($input->getArgument('model'));
		$module = strtolower($input->getArgument('module'));
		$softDelete = $input->getOption('softDelete');
		if ($allSuffix) {
			$tables = Db::getTables();
			$tables_array = [];
			foreach ($tables as $item) {
				$table = Utils::tableWithoutPrefix($item);
				if (explode('_', $table)[0] != Str::snake($input_model)) {
					continue;
				}
				$tables_array[] = $table;
				$model = Str::studly($table);
				$params = [
					'model' => 'little\\' . $module . '\\model\\' . $model,
					'model_repository' => 'little\\' . $module . '\\repository\\model\\' . $model . 'Abstract',
					'table' => Str::snake($model),
					'extra' => [
						'soft_delete' => $softDelete ? true : false,
					],
				];
				$this->create($module, $model, $params, $output);
			}
			// dd($tables_array);
		} else {
			$params = [
				'model' => 'little\\' . $module . '\\model\\' . $model,
				'model_repository' => 'little\\' . $module . '\\repository\\model\\' . $model . 'Abstract',
				'table' => Str::snake($model),
				'extra' => [
					'soft_delete' => $softDelete ? true : false,
				],
			];
			$this->create($module, $model, $params, $output);
		}
	}

	protected function create($module, $model, $params, $output)
	{
		$modelFile = App::getModuleModelDirectory($module) . $model . '.php';
		$modelTraitFile = App::getModuleDirectory($module, 'repository/model') . $model . 'Abstract' . '.php';

		$asn = 'Y';

		if (file_exists($modelFile)) {
			$asn = $this->output->ask($this->input, "Model File {$model} already exists.Are you sure to overwrite, the content will be lost(Y/N) default N") ?? 'Y';
		}

		if (strtolower($asn) == 'n') {
			exit(0);
		}

		(new Model())->done($params);

		if (file_exists($modelFile)) {
			$output->info(sprintf('%s Create Successfully!', $modelFile));
		} else {
			$output->error(sprintf('%s Create Failed!', $modelFile));
		}
		if (file_exists($modelTraitFile)) {
			$output->info(sprintf('%s Create Successfully!', $modelTraitFile));
		} else {
			$output->error(sprintf('%s Create Failed!', $modelTraitFile));
		}
	}
}
