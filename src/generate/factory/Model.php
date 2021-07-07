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

namespace littler\generate\factory;

use littler\annotation\Inject;
use littler\BaseModel;
use littler\exceptions\FailedException;
use littler\facade\FileSystem;
use littler\traits\BaseOptionsTrait;
use littler\traits\RewriteTrait;
use littler\Utils;
use Nette\PhpGenerator\PhpFile;
use think\facade\Db;
use think\helper\Str;
use think\model\concern\SoftDelete;

class Model extends Factory
{
	private $header = <<<'EOF'
		#logic 做事不讲究逻辑，再努力也只是重复犯错
		## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。
		## 只要思想不滑稽，方法总比苦难多！
		@version 1.0.0
		@author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
		@contact  littlezov@qq.com
		@link     https://github.com/littlezo
		@document https://github.com/littlezo/wiki
		@license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE

		EOF;

	/**
	 * done.
	 *
	 * @param $params
	 */
	public function done(array $params): string
	{
		$repository = $this->getRepositoryContent($params);
		$repositoryFile = $this->getGeneratePath($params['model_repository']);
		FileSystem::put($repositoryFile, $repository);
		$content = $this->getContent($params);
		$contentPath = $this->getGeneratePath($params['model']);
		if (! file_exists($contentPath)) {
			FileSystem::put($contentPath, $content);
		}
		return $contentPath;
	}

	/**
	 * 获取内容.
	 *
	 * @param $params
	 * @return bool|string|string[]
	 */
	public function getRepositoryContent($params)
	{
		if (! $params['table']) {
			throw new FailedException('params has lost～');
		}
		$table = Utils::tableWithPrefix($params['table']);
		[$className, $classNamespace] = $this->parseFilename($params['model_repository']);
		// 如果填写了表名并且没有填写模型名称 使用表名作为模型名称
		if (! $className && $table) {
			$className = ucfirst(Str::camel($table));
			$params['model'] = $params['model'] . $className;
		}
		if (! $className) {
			throw new FailedException('model name not set');
		}
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse(BaseModel::class, 'Model')
		->addUse(Inject::class)
			->addUse(BaseOptionsTrait::class)
			->addUse(RewriteTrait::class);
		$fields_type = Db::name(Utils::tableWithoutPrefix($table))->getFieldsType();
		$schema = [];
		$is_soft_delete = false;
		foreach ($fields_type as $field => $type) {
			$schema += [$field => $type];
			if ($field != 'delete_time') {
				continue;
			}
			$is_soft_delete = true;
		}
		if ($is_soft_delete) {
			$namespace->addUse(SoftDelete::class);
		}
		$class = $namespace->addClass($className)
			->setAbstract()
			->setExtends(BaseModel::class)
			->addTrait(BaseOptionsTrait::class)
			->addTrait(RewriteTrait::class);
		if ($is_soft_delete) {
			$class->addTrait(SoftDelete::class);
		}
		$fields = Db::getFields($table);
		$type_item = [];
		$is_off_create_time = true;
		$is_s_off_update_time = true;
		$pk = 'id';
		$json_field = [];
		$is_json_assoc = false;
		$write_field = [];
		foreach ($fields as $field) {
			$pos = strpos($field['type'], '(');
			$is_time = strpos($field['name'], 'time');
			$type = substr($field['type'], 0, $pos ?: strlen($field['type']));
			$arrItem = [$field['name'] => $is_time ? 'timestamp' : $type];
			if ($field['name'] == 'create_time') {
				$is_off_create_time = false;
			}
			if ($field['name'] == 'update_time') {
				$is_s_off_update_time = false;
			}
			if ($field['primary'] === true) {
				$pk = $field['name'];
			}
			if ($field['type'] === 'json') {
				$json_field[] = $field['name'];
				$is_json_assoc = true;
			}
			$write_field[] =  $field['name'];
			$type_item += $arrItem;
			$class->addComment(sprintf('@property %s $%s %s', $field['name'], $fields_type[$field['name']], $field['comment']));
		}
		$class->addProperty('name', Utils::tableWithoutPrefix($table))
			->setProtected()
			->addComment('')
			->addComment(PHP_EOL . '@var string $name 表名');
		$class->addProperty('pk', $pk)
			->setProtected()
			->addComment('')
			->addComment(PHP_EOL . '@var string $pk 主键');

		$class->addProperty('schema', $schema)
			->setProtected()
			->addComment(PHP_EOL . '@var array $schema 字段信息');
		// $class->addProperty('type', $type_item)
		// 	->setProtected()
		// 	->addComment(PHP_EOL . '@var array $type 字段类型自动转换');
		$class->addProperty('json', $json_field)
			->setProtected()
			->addComment(PHP_EOL . '@var array $json JSON类型字段');
		if ($is_json_assoc) {
			$class->addProperty('jsonAssoc', $is_json_assoc)
				->setProtected()
				->addComment(PHP_EOL . '@var array $json JSON字段自动转数组');
		}
		if ($is_off_create_time) {
			$class->addProperty('createTime', false)
				->setProtected()
				->addComment(PHP_EOL . '@var array $createTime 关闭创建时间自动写入');
		}
		if ($is_s_off_update_time) {
			$class->addProperty('updateTime', false)
				->setProtected()
				->addComment(PHP_EOL . '@var array $updateTime 关闭更新时间自动写入');
		}
		$class->addProperty('field', $write_field)
			->setPublic()
			->addComment(PHP_EOL . '@var array $field 允许写入字段');
		return $content;
	}

	/**
	 * get contents.
	 *
	 * @param $params
	 * @return string|string[]
	 */
	public function getContent($params)
	{
		$table = Utils::tableWithPrefix($params['table']);
		[$className, $classNamespace] = $this->parseFilename($params['model']);
		// 如果填写了表名并且没有填写模型名称 使用表名作为模型名称
		if (! $className && $table) {
			$className = ucfirst(Str::camel($table));
			$params['model'] = $params['model'] . $className;
		}
		if (! $className) {
			throw new FailedException('model name not set');
		}
		$repository = $params['model_repository'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($repository)
			->addClass($className)
			->setExtends($repository)
			->addComment(PHP_EOL . sprintf('%s 模型', $params['extra']['title']));

		return $content;
	}
}
