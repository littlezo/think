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

namespace littler;

/**
 * @method getList(array $data = [])
 * @method storeBy(array $data)
 * @method updateBy(int $id, array $data)
 * @method findBy(int $id, array $column = ['*'])
 * @method deleteBy(int $id)
 * @method disOrEnable(int $id)
 * @method startTrans()
 * @method rollback()
 * @method commit()
 * @method transaction(\Closure $callback)
 * @method raw($sql)
 */
abstract class BaseRepository
{
	/**
	 * 模型映射方法.
	 *
	 * @param $name
	 * @param $arguments
	 * @throws \Exception
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		// TODO: Implement __call() method.
		if (method_exists($this, 'model')) {
			return call_user_func_array([$this->model(), $name], $arguments); //$this->model()->$name(...$arguments);
		}

		throw new \Exception(sprintf('Method %s Not Found~', $name));
	}
}
