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
use littler\exceptions\FailedException;
use littler\facade\FileSystem;
use littler\Request;
use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\PhpFile;

class Service extends Factory
{
	protected $methods = [];

	/**
	 * @param $params
	 * @return bool|string|string[]
	 */
	public function done(array $params)
	{
		// dd($params);
		$path = $this->getGeneratePath($params['service']);
		// echo $this->getContent($params);
		// dd();
		try {
			if (! file_exists($path)) {
				// if ($params['table'] !== 'user_account') {
				FileSystem::put($path, $this->getContent($params));
			}
			if (file_exists($path)) {
				[$className] = $this->parseFilename($params['service']);
				$model_key = 'service.' . $params['extra']['module'] . '.' . $className;
				$service_map = include $this->getModulePath($params['service']) . 'config/serviceMap.php';
				$service = array_merge($service_map, [$model_key => $params['service']]);
				$dumper = new Dumper();
				$service_content = sprintf('<?php' . PHP_EOL . PHP_EOL . 'return %s;', $dumper->dump($service));
				FileSystem::put($this->getModulePath($params['service']) . 'config/serviceMap.php', $service_content);
			}
			return $path;
		} catch (\Throwable $exception) {
			throw new \Exception((string) $exception->getTraceAsString());
		}
	}

	/**
	 * 获取内容.
	 *
	 * @param $params
	 * @return bool|string|string[]
	 */
	public function getContent($params)
	{
		if (! $params['service']) {
			throw new FailedException('params has lost～');
		}
		[$className, $namespace] = $this->parseFilename($params['service']);
		$use_model = $params['model'];
		$is_layout = $params['extra']['is_layout'];
		// dd($is_layout);
		if (! $className) {
			throw new FailedException('未填写控制器名称');
		}
		$header = <<<'EOF'
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
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($header);
		$namespace = $content->addNamespace($namespace);
		if ($use_model) {
			$namespace->addUse($use_model);
		}
		$namespace->addUse(Request::class)->addUse(Inject::class)->addUse(\Exception::class);
		$class = $namespace->addClass($className);
		$class->addProperty('model')
			->setPrivate()
			->addComment('@Inject()')
			->addComment('@var ' . $namespace->unresolveName($use_model));
		$class->addProperty('request')
			->setPrivate()
			->addComment('@Inject()')
			->addComment('@var Request')
			->addComment('desc  Request对象 request->user 可以取当前用户信息');
		if ($is_layout) {
			$method = $class->addMethod('layout')
				->addComment('#title 布局获取')
				->addComment('@param int $type form||table 页面布局类型')
				->addComment('@return ' . $namespace->unresolveName($use_model))
				->setReturnType('array')
				->setReturnNullable()
				->setBody(
					<<<'EOF'
						if (in_array($type, ['table', 'form'], true)) {
						    switch ($type) {
						        case 'table':
						        $schema = $this->model->table_schema;
						        $schema['formConfig'] = $this->model->search_schema;
						        break;
						    case 'form':
						        $schema = $this->model->form_schema;
						        break;
						    default:
						        $schema =null;
						        break;
						    }
						    if ($schema) {
						        return $schema;
						    }
						}
						throw new Exception('类型错误', 9500901);
						EOF
				);
			$method->addParameter('type')
			->setType('string');
			// dd('debug');
		}

		$method = $class->addMethod('paginate')
			->addComment('#title 分页')
			->addComment('@return ' . $namespace->unresolveName($use_model))
			->setReturnType('object')
			->setReturnNullable()
			->setBody('return $this->model->getList();');
		$method = $class->addMethod('list')
			->addComment('#title 列表')
			->addComment('@return ' . $namespace->unresolveName($use_model))
			->setReturnType('object')
			->setReturnNullable()
			->setBody('return $this->model->getList(false);');
		$method = $class->addMethod('info')
			->addComment('#title 详情')
			->addComment('@param int $id 数据主键')
			->addComment('@return ' . $namespace->unresolveName($use_model))
			->setReturnType('object')
			->setReturnNullable()
			->setBody('return $this->model->findBy($id);');
		$method->addParameter('id')
			->setType('int');
		$method = $class->addMethod('save')
			->addComment('#title 保存')
			->addComment('@param array $args 待写入数据')
			->addComment('@return int||bool')
			// ->setReturnType('bool')
			->setReturnNullable()
			->setBody('return $this->model->storeBy($args);');
		$method->addParameter('args')
			->setType('array');
		$method = $class->addMethod('update')
			->addComment('#title 更新')
			->addComment('@param int $id ID')
			->addComment('@param array $args 待更新的数据')
			->addComment('@return int|bool')
			// ->setReturnType('bool')
			->setReturnNullable()
			->setBody('return $this->model->updateBy($id, $args);');
		$method->addParameter('id')
			->setType('int');
		$method->addParameter('args')
			->setType('array');
		$method = $class->addMethod('delete')
			->addComment('#title 删除')
			->addComment('@param int $id ID')
			->addComment('@return bool')
			->setReturnType('bool')
			->setReturnNullable()
			->setBody('return $this->model->deleteBy($id);');
		$method->addParameter('id')
			->setType('int');

		return $content;
	}
}
