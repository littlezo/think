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
use littler\Tree;
use littler\Utils;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;

class ExportDataCommand extends Command
{
	protected $table;

	protected function configure()
	{
		// 指令配置
		$this->setName('export')
			->addArgument('table', Argument::REQUIRED, 'export tables')
			->addOption('pid', '-p', Option::VALUE_REQUIRED, 'parent level name')
			->addOption('module', '-m', Option::VALUE_REQUIRED, 'module name')
			->setDescription('Just for little export data');
	}

	protected function execute(Input $input, Output $output)
	{
		//$table = // Utils::tablePrefix() .
		$table = $input->getArgument('table');
		$parent = $input->getOption('pid');
		$module = $input->getOption('module');

		if ($module) {
			$data = Db::name($table)->where('deleted_time', 0)
				->where('module', $module)
				->select()
				->toArray();
		} else {
			$data = Db::name($table)->where('deleted_time', 0)
				->select()
				->toArray();
		}

		if ($parent) {
			$data = Tree::done($data, 0, $parent);
		}

		if ($module) {
			$data = 'return ' . var_export($data, true) . ';';
			$this->exportSeed($data, $module);
		} else {
			file_put_contents(root_path() . DIRECTORY_SEPARATOR . $table . '.php', "<?php\r\n return " . var_export($data, true) . ';');
		}
		$output->info('succeed!');
	}

	protected function exportSeed($data, $module)
	{
		$stub = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'permissionSeed.stub');

		$class = ucfirst($module) . 'MenusSeed';

		$stub = str_replace('{CLASS}', $class, $stub);

		file_put_contents(App::moduleSeedsDirectory($module) . $class . '.php', str_replace('{DATA}', $data, $stub));
	}
}
