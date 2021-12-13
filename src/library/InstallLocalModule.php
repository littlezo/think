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

namespace littler\library;

use littler\App;
use think\facade\Console;

class InstallLocalModule
{
	protected $module;

	public function __construct($module)
	{
		$this->module = $module;
	}

	/**
	 * 查找.
	 *
	 * @return bool
	 */
	public function done()
	{
		if ($this->findModuleInPermissions()) {
			return false;
		}
		$this->installModuleTables();
		$this->installModuleSeeds();
		$this->enableModule();
		return true;
	}

	/**
	 * 本地模块是否存在.
	 *
	 * @return bool
	 */
	public function localModuleExist()
	{
		return in_array($this->module, array_column(App::getModulesInfo(true), 'value'), true);
	}

	/**
	 * 模块是否开启.
	 *
	 * @return false|mixed
	 */
	public function isModuleEnabled()
	{
		return in_array($this->module, array_column($this->getLocalModulesInfo(false), 'name'), true);
	}

	/**
	 * 获取本地模块信息.
	 *
	 * @param bool $enabled
	 * @return array
	 */
	public function getLocalModulesInfo($enabled = true)
	{
		$modules = App::getModulesInfo(true);

		$info = [];
		foreach ($modules as $module) {
			$moduleInfo = App::getModuleInfo(App::directory() . $module['value']);
			// 获取全部
			if ($enabled) {
				$info[] = [
					'name' => $module['value'],
					'title' => $module['title'],
					'enable' => $moduleInfo['enable'],
				];
			} else {
				// 获取未开启的
				if (! $moduleInfo['enable']) {
					$info[] = [
						'name' => $module['value'],
						'title' => $module['title'],
						'enable' => $moduleInfo['enable'],
					];
				}
			}
		}

		return $info;
	}

	/**
	 * 查找模块.
	 *
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @return bool
	 */
	public function findModuleInPermissions()
	{
		return Menu::withTrashed()->where('module', $this->module)->find() ? true : false;
	}

	/**
	 * 启用模块.
	 */
	public function enableModule()
	{
		App::updateModuleInfo($this->module, ['enable' => true]);

		app(Menu::class)->restore(['module' => trim($this->module)]);
	}

	/**
	 * 禁用模块.
	 */
	public function disableModule()
	{
		App::updateModuleInfo($this->module, ['enable' => false]);

		Menu::destroy(function ($query) {
			$query->where('module', trim($this->module));
		});
	}

	/**
	 * 创建模块表.
	 */
	public function installModuleTables()
	{
		Console::call('lz-migrate:run', [$this->module]);
	}

	/**
	 * 初始化模块数据.
	 */
	public function installModuleSeeds()
	{
		Console::call('lz-seed:run', [$this->module]);
	}

	/**
	 * 回滚模块表.
	 */
	public function rollbackModuleTable()
	{
		Console::call('lz-migrate:rollback', [$this->module, '-f']);
	}
}
