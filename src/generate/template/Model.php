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

namespace littler\generate\template;

class Model
{
	use Content;

	public function createModel($model, $table)
	{
		return <<<TMP
			class {$model} extends Model
			{
			    {CONTENT}
			}
			TMP;
	}

	public function useTrait($hasDeletedAt = true)
	{
		if (! $hasDeletedAt) {
			return <<<'TMP'
				use BaseOptionsTrait,ScopeTrait;


				TMP;
		}
	}

	public function uses($hasDeletedAt = true)
	{
		if ($hasDeletedAt) {
			return <<<'TMP'
				use littler\BaseModel as Model;


				TMP;
		}
		return <<<'TMP'
			use littler\traits\BaseOptionsTrait;


			TMP;
	}

	/**
	 * name.
	 *
	 * @param $name
	 * @return string
	 */
	public function name($name)
	{
		if ($name) {
			return <<<TMP
				protected \$name = '{$name}';


				TMP;
		}
	}

	/**
	 * field.
	 *
	 * @param $field
	 * @return string
	 */
	public function field($field)
	{
		if ($field) {
			return <<<TMP
				    protected \$field = [
				        {$field}
				    ];


				TMP;
		}
	}

	/**
	 * 一对一关联.
	 *
	 * @param $model
	 * @param string $foreignKey
	 * @param string $pk
	 * @return string
	 */
	public function hasOne($model, $foreignKey = '', $pk = '')
	{
		$func = lcfirst($model);

		return <<<TMP
			    public function {$func}()
			    {
			       return \$this->hasOne({$model}::class{$this->keyRelate($foreignKey, $pk)});
			    }
			TMP;
	}

	/**
	 * @param $model
	 * @param string $foreignKey
	 * @param string $pk
	 * @return string
	 */
	public function hasMany($model, $foreignKey = '', $pk = '')
	{
		$func = lcfirst($model);

		return <<<TMP
			    public function {$func}()
			    {
			       return \$this->hasMany({$model}::class{$this->keyRelate($foreignKey, $pk)});
			    }
			TMP;
	}

	/**
	 * 远程一对多.
	 *
	 * @param $model
	 * @param $middleModel
	 * @param string $foreignKey
	 * @param string $pk
	 * @param string $middleRelateId
	 * @param string $middleId
	 * @return string
	 */
	public function hasManyThrough($model, $middleModel, $foreignKey = '', $pk = '', $middleRelateId = '', $middleId = '')
	{
		$func = lcfirst($model);

		return <<<TMP
			    public function {$func}()
			    {
			       return \$this->hasManyThrough({$model}::class, {$middleModel}::class{$this->keyRelate($foreignKey, $pk, $middleRelateId, $middleId)});
			    }
			TMP;
	}

	/**
	 * 远程一对一
	 *
	 * @param $model
	 * @param $middleModel
	 * @param string $foreignKey
	 * @param string $pk
	 * @param string $middleRelateId
	 * @param string $middleId
	 * @return string
	 */
	public function hasOneThrough($model, $middleModel, $foreignKey = '', $pk = '', $middleRelateId = '', $middleId = '')
	{
		$func = lcfirst($model);

		return <<<TMP
			    public function {$func}()
			    {
			       return \$this->hasOneThrough({$model}::class, {$middleModel}::class{$this->keyRelate($foreignKey, $pk, $middleRelateId, $middleId)});
			    }
			TMP;
	}

	/**
	 * 多对多关联.
	 *
	 * @param $model
	 * @param string $table
	 * @param string $foreignKey
	 * @param string $relateKey
	 * @return string
	 */
	public function belongsToMany($model, $table = '', $foreignKey = '', $relateKey = '')
	{
		$func = lcfirst($model);

		$table = ! $table ?: ',' . $table;

		$relateKey = ! $relateKey ?: ',' . $relateKey;

		return <<<TMP
			    public function {$func}()
			    {
			       return \$this->hasOneThrough({$model}::class{$table}{$this->keyRelate($foreignKey)}{$relateKey});
			    }
			TMP;
	}

	/**
	 * 模型关联key.
	 *
	 * @param string $foreignKey
	 * @param string $pk
	 * @param string $middleRelateId
	 * @param string $middleId
	 * @return string
	 */
	public function keyRelate($foreignKey = '', $pk = '', $middleRelateId = '', $middleId = '')
	{
		return ! $foreignKey ?: ',' . $foreignKey .
			   ! $middleRelateId ?: ',' . $middleRelateId .
			   ! $pk ?: ',' . $pk .
			   ! $middleId ?: ',' . $middleId;
	}
}
