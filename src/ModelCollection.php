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

use littler\library\excel\Excel;
use littler\library\excel\ExcelContract;
use think\facade\Cache;
use think\model\Collection;

class ModelCollection extends Collection
{
	/**
	 * tree 结构.
	 *
	 * @param int $pid
	 * @param string $pidField
	 * @param string $children
	 */
	public function toTree($pid = 0, $pk = 'id', $pidField = 'parent', $children = 'children'): array
	{
		return Tree::done($this->toArray(), $pid, $pk, $pidField, $children);
	}

	/**
	 * 导出数据.
	 *
	 * @param $header
	 * @param string $path
	 * @param string $disk
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @return mixed|string[]
	 */
	public function export($header, $path = '', $disk = 'local'): array
	{
		$excel = new class($header, $this->items) implements ExcelContract {
			protected $headers;

			protected $sheets;

			public function __construct($headers, $sheets)
			{
				$this->headers = $headers;

				$this->sheets = $sheets;
			}

			public function headers(): array
			{
				// TODO: Implement headers() method.
				return $this->headers;
			}

			public function sheets()
			{
				// TODO: Implement sheets() method.
				return $this->sheets;
			}
		};

		if (! $path) {
			$path = Utils::publicPath('exports');
		}

		return (new Excel())->save($excel, $path, $disk);
	}

	/**
	 * 缓存 collection.
	 *
	 * @param $key
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function cache($key, int $ttl = 0, string $store = 'redis'): bool
	{
		return Cache::store($store)->set($key, $this->items, $ttl);
	}

	/**
	 * 获取当前级别下的所有子级.
	 *
	 * @param string $parentFields
	 * @param string $column
	 */
	public function getAllChildrenIds(array $ids, $parentFields = 'parent', $column = 'id'): array
	{
		array_walk($ids, function (&$item) {
			$item = intval($item);
		});

		$childIds = $this->whereIn($parentFields, $ids)->column($column);

		if (! empty($childIds)) {
			$childIds = array_merge($childIds, $this->getAllChildrenIds($childIds, $parentFields, $column));
		}

		return $childIds;
	}

	/**
	 * implode.
	 */
	public function implode(string $column = '', string $separator = ','): string
	{
		return implode($separator, $column ? array_column($this->items, $column) : $this->items);
	}
}
