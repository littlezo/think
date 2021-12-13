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
use Nette\PhpGenerator\Dumper;
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

	private $actions = <<<'EOF'
		"[
		    {
		        icon: 'clarity:note-edit-line',
		        label: '修改',
		        auth: '%s:update',
		        onClick: handleEdit.bind(null, record),
		    },
		    {
		        label: '删除',
		        icon: 'ant-design:delete-outlined',
		        color: 'error',
		        auth: '%s:delete',
		        popConfirm: {
		            title: '是否确认删除',
		            confirm: handleDelete.bind(null, record),
		        },
		    },
		]",
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
		$path = $this->getGeneratePath($params['model']);
		if (! file_exists($path)) {
			// dd($params['table']);
			// if ($params['table'] !== 'user_account') {
			FileSystem::put($path, $content);
		}
		// if (file_exists($path)) {
		[$className] = $this->parseFilename($params['model']);
		$model_key = 'model.' . $params['extra']['module'] . '.' . $className;
		$model_map = include $this->getModulePath($params['model']) . 'config/modelMap.php';
		$model = array_merge($model_map, [$model_key => $params['model']]);
		$dumper = new Dumper();
		$model_content = sprintf('<?php' . PHP_EOL . PHP_EOL . 'return %s;', $dumper->dump($model));
		FileSystem::put($this->getModulePath($params['model']) . 'config/modelMap.php', $model_content);
		// }
		return $path;
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
			->setPublic()
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
		$class = $namespace->addUse($repository)
			->addClass($className)
			->setExtends($repository)
			->addComment(PHP_EOL . sprintf('%s 模型', $params['extra']['title']));
		$class->addProperty('with', [])
			->setPublic()
			->addComment(PHP_EOL . '@var array 关联预载');
		$class->addProperty('table_schema', $this->tableSchema($table))
			->setPublic()
			->addComment(PHP_EOL . '@var array 列表字段映射');
		$class->addProperty('search_schema', $this->searchSchema($table))
			->setPublic()
			->addComment(PHP_EOL . '@var array 搜索表单字段映射  具体字段规则参见 快速搜索定义 ');
		$class->addProperty('form_schema', $this->formSchema($table))
			->setPublic()
			->addComment(PHP_EOL . '@var array 增加表单字段映射');
		$class->addProperty('without', ['password', 'passwd', 'pay_passwd', 'pay_password'])
			->setPublic()
			->addComment(PHP_EOL . '@var array 排除展示字段');
		return $content;
	}

	protected function tableSchema($table)
	{
		$tag = Utils::tableWithoutPrefix($table);
		$pos = strpos($tag, '_', );
		$auth = substr($tag, 0, $pos) . ':' . substr($tag, $pos + 1);

		$fields = Db::getFields($table);
		$dumper = new Dumper();
		$actions = [
				[
					'icon' => 'clarity:note-edit-line',
					'label' => '修改',
					'auth' => "$auth:update",
					'onClick' => 'handleEdit.bind(null, record)',
				],
				[
					'label' => '删除',
					'icon' => 'ant-design:delete-outlined',
					'color' => 'error',
					'auth' => "$auth:delete",
					'popConfirm' => [
						'title' => '是否确认删除',
						'confirm' => 'handleDelete.bind(null, record)',
					],
				],
			];
		$tableSchema = [
			'columns' => [],
			'formConfig' => [],
			'pagination' => true,
			'striped' => true,
			'useSearchForm' => true,
			'showTableSetting' => true,
			'bordered' => true,
			'showIndexColumn' => false,
			'canResize' => true,
			'rowKey' => 'id',
			'searchInfo' => ['order' => 'asc'],
			'actionColumn' => [
				'width' => 100,
				'title' => '操作',
				'dataIndex' => 'action',
				'slots' => ['customRender' => 'action'],
				'fixed' => 'right',
			],
			// sprintf($this->actions, $auth, $auth)
			'dropActions' =>  json_encode($actions, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES),

			'actions' => '[]',
		];
		// dd($tableSchema);
		$children = [];
		foreach ($fields as $field) {
			$field_type = explode(':', str_replace([' ', '(', ')'], ':', $field['type']));
			$title = ($field['primary'] ? 'ID' : $field['comment']) ?: Str::studly($field['name']);
			$width = 80;
			$defaultHidden = true;
			$customRender = null;
			$fixed = false;
			if ($field_type == 'json') {
				continue;
			}
			if (stripos($field['name'], 'delete')) {
				continue;
			}
			if (stripos($field['name'], 'pass')) {
				// dd($field['name']);
				continue;
			}

			if (! $field['primary']) {
				if (in_array($field_type[0], ['int', 'tinyint', 'smallint', 'mediumint', 'bigint', 'float', 'double', 'decimal'], true)) {
					if (strpos($field['name'], 'time')) {
						$width = 120;
					} else {
						$width = 100;
					}
					$defaultHidden = false;
				} elseif (in_array($field_type[0], ['char', 'varchar'], true)) {
					$width = 160;
					if ($field_type[1] > 16) {
						$width = 180;
					}
					$defaultHidden = false;
				}
			} else {
				$fixed = 'left';
				$defaultHidden = false;
				$tableSchema['rowKey'] = $field['name'];
			}
			if (strpos($field['name'], 'openid')) {
				$defaultHidden = true;
			}
			if (strpos($field['name'], 'unionid')) {
				$defaultHidden = true;
			}

			// if (strpos($field['name'], 'img')) {
			// 	$customRender = 'img';
			// }
			// if (strpos($field['name'], 'imgs')) {
			// 	$customRender = 'imgs';
			// }
			// if (in_array($field['name'], ['avatar', 'headimg'], true)) {
			// 	$customRender = 'avatar';
			// 	$width = 150;
			// }
			$schema = [
				'title' => $title,
				'dataIndex' => $field['name'],
				'width' => $width,
				'fixed' => $fixed,
				'align' => 'center',
				'defaultHidden' => $defaultHidden,
			];
			// if ($customRender) {
			// 	$schema['slots'] = ['customRender' => $customRender];
			// }
			$tableSchema['columns'][] = $schema;

			// if ($defaultHidden == false) {
			// 	$tableSchema[] =$schema;
			// } else {
			// 	$children[] =$schema;
			// }
		}
		// $tableSchema['children'] = $children;
		return $tableSchema;
		// dd($tableSchema);
	}

	protected function searchSchema($table)
	{
		$fields = Db::getFields($table);
		$searchSchema = [
			'labelWidth' => 100,
			'baseColProps' => [
				'xxl' => 6,
				'xl' => 8,
				'lg' => 12,
				'md' => 34,
			],
			'schemas' => [],
		];

		foreach ($fields as $field) {
			$field_type = explode(':', str_replace([' ', '(', ')'], ':', $field['type']));
			$title = ($field['primary'] ? 'ID' : $field['comment']) ?: Str::studly($field['name']);
			if (! $field['primary']) {
				continue;
			}
			$schema = [
				'field' => $field['name'],
				'label' => $title,
				'component' => 'Input',
			];
			$searchSchema['schemas'][] = $schema;
		}
		return $searchSchema;
	}

	protected function formSchema($table)
	{
		$fields = Db::getFields($table);
		$formSchema = [
			'labelWidth' => 120,
			'baseColProps' => [
				'xxl' => 6,
				'xl' => 8,
				'lg' => 12,
				'md' => 34,
			],
			'schemas' => [],
		];

		foreach ($fields as $field) {
			$field_type = explode(':', str_replace([' ', '(', ')'], ':', $field['type']));
			$title = ($field['primary'] ? 'ID' : $field['comment']) ?: Str::studly($field['name']);
			if ($field['primary']) {
				continue;
			}

			if (strpos($field['name'], 'openid')) {
				continue;
			}
			if (strpos($field['name'], 'unionid')) {
				continue;
			}

			$required = $field['notnull'] ?: false;
			$schema = [
				'field' => $field['name'],
				'label' => $title,
				'component' => 'Input',
				'required' => $required,
			];
			$formSchema['schemas'][] = $schema;
		}
		return $formSchema;
	}
}
