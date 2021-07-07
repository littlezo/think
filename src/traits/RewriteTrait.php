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

use littler\ModelCollection;
use think\Collection;

/**
 * 重写 think\Model 的方法.
 *
 * Trait RewriteTrait
 */
trait RewriteTrait
{
	/**
	 * 初始化.
	 *
	 * Model constructor.
	 */
	public function __construct(array $data = [])
	{
		parent::__construct($data);

		$this->hidden = array_merge($this->hidden, $this->defaultHiddenFields());
	}

	/**
	 * 重写 hidden 方法，支持合并 hidden 属性.
	 *
	 * @return $this
	 */
	public function hidden(array $hidden = [])
	{
		/*
		 * 合并属性
		 */
		if (! count($this->hidden)) {
			$this->hidden = array_merge($this->hidden, $hidden);

			return $this;
		}

		$this->hidden = $hidden;

		return $this;
	}

	/**
	 * rewrite collection.
	 *
	 * @param array|iterable $collection
	 * @return mixed|ModelCollection
	 */
	public function toCollection(iterable $collection = [], string $resultSetType = null): Collection
	{
		$resultSetType = $resultSetType ?: $this->resultSetType;

		if ($resultSetType && strpos($resultSetType, '\\') !== false) {
			$collection = new $resultSetType($collection);
		} else {
			$collection = new ModelCollection($collection);
		}

		return $collection;
	}

	/**
	 * hidden model fields.
	 */
	protected function defaultHiddenFields(): array
	{
		if ($this->hasField($this->deleteTime)) {
			return [$this->deleteTime];
		}

		return [];
	}
}
