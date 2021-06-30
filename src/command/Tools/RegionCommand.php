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

use littler\facade\FileSystem;
use littler\generate\support\Table;
use littler\generate\support\TableColumn;
use littler\library\Compress;
use littler\library\ProgressBar;
use littler\library\Zip;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\facade\Db;

class RegionCommand extends Command
{
	protected function configure()
	{
		// 指令配置
		$this->setName('region')
			->addOption('rollback', '-r', Argument::REQUIRED, '删除 Region')
			->setDescription('create region data');
	}

	protected function execute(Input $input, Output $output)
	{
		if ($input->hasOption('rollback')) {
			$this->deleteRegion();
		} else {
			$output->info('start create region');

			$this->createRegion();

			$output->info('create region successfully');

			$this->importRegionData();
		}
	}

	protected function createRegion()
	{
		$table = new Table('region');

		if ($table::exist()) {
			$this->output->error('table [region] has existed!');
			exit;
		}

		if (! $table::create('id', 'InnoDB', '地区表')) {
			$this->output->error('table [region] create failed!');
			exit;
		}

		$column = new TableColumn();

		$table::addColumn($column->int('parent', 10)->setDefault(0)->setComment('父级'));
		$table::addColumn($column->tinyint('level', 1)->setDefault(1)->setComment('等级'));
		$table::addColumn($column->varchar('name', 50)->setDefault('')->setComment('地区名称'));
		$table::addColumn($column->varchar('initial', 100)->setDefault('')->setComment('首字母'));
		$table::addColumn($column->varchar('pinyin', 100)->setDefault('')->setComment('拼音'));
		$table::addColumn($column->varchar('city_code', 100)->setDefault('')->setComment('城市编码'));
		$table::addColumn($column->varchar('ad_code', 100)->setDefault('')->setComment('区域编码'));
		$table::addColumn($column->varchar('lng_lat', 500)->setDefault('')->setComment('中心经纬度'));

		$table::addIndex(['level']);
	}

	protected function deleteRegion()
	{
		$table = new Table('region');

		if (! $table::drop()) {
			$this->output->error('table [region] drop failed!');
			exit;
		}

		$this->output->info('table [region] has deleted!');
	}

	protected function importRegionData()
	{
		try {
			$compress = new Compress();

			$this->output->info('start downloading');

			$regionZip = runtime_path() . DIRECTORY_SEPARATOR . 'region.zip';
			$regionJson = runtime_path() . DIRECTORY_SEPARATOR . 'region.json';

			$compress->savePath($regionZip)
				->download('http://json.think-region.yupoxiong.com/region.json.zip');

			if (! FileSystem::exists($regionZip)) {
				$this->output->error('import failed! Json data download failed');
			}

			(new Zip())->make($regionZip, \ZipArchive::CREATE)
				->extractTo(runtime_path())->close();

			$region = \json_decode(file_get_contents($regionJson, true));

			$this->output->info('start import region data');

			Db::startTrans();

			$data = [];

			$total = count($region);

			$bar = new ProgressBar($this->output, $total);
			$bar->start();
			foreach ($region as $k => $r) {
				$data[] = [
					'id' => $r->id,
					'parent' => $r->parent,
					'level' => $r->level,
					'name' => $r->name,
					'initial' => $r->initial,
					'pinyin' => $r->pinyin,
					'city_code' => $r->citycode,
					'ad_code' => $r->adcode,
					'lng_lat' => $r->lng_lat,
				];

				if (count($data) >= 500) {
					if (! Db::name('region')->insertAll($data)) {
						Db::rollback();
						break;
					}
					$data = [];
					$bar->advance(500);
				}

				if ($total == $k + 1) {
					if (! Db::name('region')->insertAll($data)) {
						Db::rollback();
						break;
					}
					$bar->advance(count($data));
				}
			}

			$bar->finished();
			Db::commit();

			unlink($regionZip);
			unlink($regionJson);
			$this->output->info(PHP_EOL . 'import region data successfully');
		} catch (\Exception $e) {
			$this->output->error($e->getMessage());
			exit();
		}
	}
}
