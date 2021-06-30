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
namespace littler\generate;

use littler\App;
use littler\exceptions\FailedException;
use littler\generate\factory\Controller;
use littler\generate\factory\Event;
use littler\generate\factory\Model;
use littler\generate\factory\Module;
use littler\generate\factory\Route;
use littler\generate\factory\Service;
use littler\library\Composer;
use littler\Utils;
use think\facade\Db;
use think\helper\Str;

class BatchGenerator
{
	public const NEED_PACKAGE = 'nikic/php-parser';

	/**
	 * generate.
	 *
	 * @param $params
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\db\exception\DataNotFoundException
	 */
	public function done($namespace, $layer, $auth)
	{
		$message = [];
		// 判断是否安装了扩展包
		if (! (new Composer())->hasPackage(self::NEED_PACKAGE)) {
			throw new FailedException(sprintf('you must use [ composer require --dev %s]', self::NEED_PACKAGE));
		}
		$tables = Db::getTables();
		$tables_array = [];
		$generate_items = [];
		$ignore_table = [];
		foreach ($tables as $item) {
			$table = Utils::tableWithoutPrefix($item);
			$table_info = explode('_', $table);
			$module = explode('_', $table)[0];
			$table_comment = sprintf(
				"Select table_name %s ,TABLE_COMMENT from INFORMATION_SCHEMA.TABLES Where table_schema = '%s' AND table_name LIKE '%s'",
				$item,
				config('database.connections.mysql.database'),
				$item
			);
			$title = Db::query($table_comment);
			if (in_array('migrations', $table_info, true)) {
				continue;
			}
			$tables_array[] = $table;
			$name = Str::studly($table);
			if (isset($table_info[1])) {
				$generate_items[$module]['info']['ignore'][] = Str::snake($name);
			} else {
				$generate_items[$module]['info'] = [
					'title' => $title[0]['TABLE_COMMENT'] ?: $name,
					'namespace' => $namespace,
					'module' => $module,
					'layer' => $layer,
					'auth' => $auth,
					'ignore' => [],
				];
			}
			$generate_items[$module]['generate'][] = [
				'model' => sprintf('%s\\%s\\model\\%s', $namespace, $module, $name),
				'model_repository' =>  sprintf('%s\\%s\\repository\\model\\%sAbstract', $namespace, $module, $name),
				'controller' => sprintf('%s\\%s\\%s\\controller\\%s', $namespace, $module, $layer, isset($table_info[1]) ? $name : 'Index'),
				'controller_repository' => sprintf('%s\\%s\\repository\\%s\\%sTrait', $namespace, $module, $layer, $name),
				'event' =>  sprintf('%s\\%s\\event\\%s', $namespace, $module, $name),
				'service' =>  sprintf('%s\\%s\\service\\%s\\%sService', $namespace, $module, $layer, $name),
				'table' => Str::snake($name),
				'extra' => [
					'soft_delete' => true,
					'not_route' => false,
					'title' => $title[0]['TABLE_COMMENT'] ?: $name,
					'namespace' => $namespace,
					'module' => $module,
					'layer' => $layer,
					'auth' => $auth,
				],
			];
		}
		foreach ($generate_items as $item) {
			$module_info = App::getModuleInfo($item['info']['module']);
			$module_ignore = $module_info['ignore'] ?? false;
			if (! $module_ignore) {
				(new Module())->done($item['info']);
				$module_ignore = $item['info']['ignore'];
			}
			foreach ($item['generate'] as $generate) {
				// if ($generate['table'] =='goods') {
				// 	dd($module_ignore);
				// }
				if (! in_array($generate['table'], (array) $module_ignore, true)) {
					$message[] = $this->execute($generate);
				} else {
					$generate['controller']=null;
					$generate['controller_repository']=null;
					// dd($generate);
					$message[] = $this->execute($generate);
				}
			}
		}
		return 'generate successfully';
	}

	protected function execute($params)
	{
		$message = [];
		$files = [];
		try {
			if ($params['service']) {
				$files[] = (new Service())->done($params);
				array_push($message, 'service created successfully');
			}

			if ($params['model']) {
				$files[] = (new Model())->done($params);
				array_push($message, 'model created successfully');
			}
			if ($params['controller']) {
				$files[] = (new Controller())->done($params);
				array_push($message, 'controller created successfully');
			}
			if ($params['event']) {
				(new Event())->done($params);
				// array_push($message, 'event created successfully');
			}
			// if ($params['controller']) {
			// 	(new Route())->controller($params['controller'])
			// 		->restful(true)
			// 		->layer('admin')
			// 		->done($params);
			// }
			return 'success';
		} catch (\Throwable $exception) {
			throw new FailedException((string) $exception->getMessage());
		}

		return $message;
	}
}
