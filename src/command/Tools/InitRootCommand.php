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

use little\permissions\model\Users;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class InitRootCommand extends Command
{
	protected $table;

	protected function configure()
	{
		// 指令配置
		$this->setName('lz:init')
			->setDescription('backup data you need');
	}

	protected function execute(Input $input, Output $output)
	{
		if ($user = Users::where('id', config('little.permissions.super_admin_id'))->find()) {
			$user->password = 'little@admin';
			$user->save();
		}
	}
}
