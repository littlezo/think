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

use littler\exceptions\FailedException;
use littler\generate\factory\Controller;
use littler\generate\factory\Migration;
use littler\generate\factory\Model;
use littler\generate\factory\Route;
use littler\generate\factory\SQL;
use littler\generate\support\Table;
use littler\library\Composer;
use littler\Utils;

class Generator
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
	public function done($params): array
	{
		// 判断是否安装了扩展包
		if (! (new Composer())->hasPackage(self::NEED_PACKAGE)) {
			throw new FailedException(sprintf('you must use [ composer require --dev %s]', self::NEED_PACKAGE));
		}

		$params = \json_decode($params['data'], true);

		[$controller, $model] = $this->parseParams($params);

		$message = [];

		$files = [];
		$migration = '';

		try {
			if ($params['create_controller']) {
				$files[] = (new Controller())->done($controller);
				array_push($message, 'controller created successfully');
			}

			if ($params['create_table']) {
				(new SQL())->done($model);
				array_push($message, 'table created successfully');
			}

			if ($params['create_model']) {
				$files[] = (new Model())->done($model);
				array_push($message, 'model created successfully');
			}

			if ($params['create_migration']) {
				$migration = (new Migration())->done([$controller['module'], $model['table']]);
				array_push($message, 'migration created successfully');
			}

			// 只有创建了 Controller 最后成功才写入 route
			// if ($params['create_controller']) {
			//     (new Route())->controller($controller['controller'])
			//         ->restful($controller['restful'])
			//         ->done();
			// }
		} catch (\Throwable $exception) {
			if (! $exception instanceof TableExistException) {
				$this->rollback($files, $migration);
			}

			throw new FailedException($exception->getMessage());
		}

		return $message;
	}

	/**
	 * preview.
	 *
	 * @param $params
	 * @return bool|string|string[]
	 */
	public function preview($params)
	{
		$type = $params['type'];

		$params = \json_decode($params['data'], true);

		[$controller, $model] = $this->parseParams($params);

		switch ($type) {
			case 'controller':
				return (new Controller())->getControllerContent($controller);
			case 'model':
				return (new Model())->getContent($model);
			default:
				break;
		}
	}

	/**
	 * parse params.
	 *
	 * @param $params
	 * @return array[]
	 */
	protected function parseParams($params): array
	{
		$module = $params['controller']['module'] ?? false;

		if (! $module) {
			throw new FailedException('请设置模块');
		}

		$controller = [
			'module' => $module,
			'model' => $params['controller']['model'] ?? '',
			'controller' => $params['controller']['controller'] ?? '',
			'restful' => $params['controller']['restful'],
		];

		$table = $params['controller']['table'] ?? '';

		if ($table) {
			$table = Utils::tableWithPrefix($table);
		}

		$model = [
			'table' => $table,
			'model' => $params['controller']['model'] ?? '',
			'sql' => $params['table_fields'],
			'extra' => $params['table_extra'],
		];

		return [$controller, $model];
	}

	/**
	 * 回滚.
	 *
	 * @param $files
	 * @param $migration
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	protected function rollback($files, $migration)
	{
		if (Table::exist()) {
			Table::drop();
		}

		foreach ($files as $file) {
			unlink($file);
		}

		if ($migration && unlink($migration)) {
			$model = new class() extends \think\Model {
				protected $name = 'migrations';
			};

			$migration = $model->order('version', 'desc')->find();
			$model->where('version', $migration->version)->delete();
		}
	}
}
