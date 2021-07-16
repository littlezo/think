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

namespace littler\traits;

trait WithTrait
{
	/**
	 * @time 2021年05月28日
	 * @param $query
	 * @return void
	 */
	public function scopeWithRelation($query)
	{
		// dd(property_exists($this, 'with'));

		if (property_exists($this, 'with') && ! empty($this->with)) {
			$query->with($this->with);
		}
	}

	/**
	 * @time 2021年05月28日
	 * @return $this
	 */
	public function withoutRelation(string $withRelation)
	{
		$withes = $this->getOptions('with');

		foreach ($withes as $k => $item) {
			if ($item === $withRelation) {
				unset($withes[$k]);
				break;
			}
		}

		return $this->setOption('with', $withes);
	}

	/**
	 * @time 2021年05月28日
	 * @return $this
	 */
	public function withOnlyRelation(string $withRelation)
	{
		return $this->with($withRelation);
	}

	/**
	 * @time 2021年05月28日
	 * @return mixed
	 */
	protected function autoWithRelation()
	{
		if (property_exists($this, 'globalScope')) {
			array_push($this->globalScope, 'withRelation');
		}
		$this->scope('scopeWith');
		if (property_exists($this, 'with')) {
			return $this->with($this->with);
		}

		return $this;
	}
}
