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

use littler\traits\BaseOptionsTrait;
use littler\traits\RewriteTrait;
use littler\traits\WithTrait;

/**
 * @mixin Query
 * Class Model
 */
abstract class BaseModel extends \think\Model
{
	use BaseOptionsTrait;
	use RewriteTrait;
	use WithTrait;

	protected $createTime = 'create_time';

	protected $updateTime = 'update_time';

	protected $deleteTime = 'delete_time';

	protected $defaultSoftDelete = 0;

	protected $autoWriteTimestamp = true;

	public function __construct(array $data = [])
	{
		parent::__construct($data);

		if (method_exists($this, 'autoWithRelation')) {
			$this->autoWithRelation();
		}
	}

	/**
	 * 是否有 field.
	 *
	 * @return bool
	 */
	public function hasField(string $field)
	{
		return property_exists($this, 'field') && in_array($field, $this->field, true);
	}

	public static function onBeforeInsert($data)
	{
		// 新增前移除主键
		$pk = $data->getAutoPk();
		unset($data->{$pk});
		return $data;
	}

	public static function onBeforeWrite($data)
	{
		// 写入前移除空字段
		return Utils::filterEmptyValue($data);
	}
}
