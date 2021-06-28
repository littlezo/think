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

use littler\exceptions\FailedException;
use littler\facade\FileSystem;
use littler\Request;
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
		if (strpos($params['service'], 'Has')) {
			return false;
		}
		// 写入成功之后
		$path = $this->getGeneratePath($params['service']);
		try {
			// if (! file_exists($path)) {
			FileSystem::put($path, $this->getContent($params));
			// }
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
		$file = new PhpFile();
		$file->setStrictTypes();
		$file->addComment($header);
		$namespace = $file->addNamespace($namespace);
		if ($use_model) {
			$namespace->addUse($use_model);
		}
		$namespace->addUse(Request::class);
		$class = $namespace->addClass($className);
		$class->addProperty('model')
			->setPrivate()
			->addComment('@Inject()')
			->addComment('@var ' . $namespace->unresolveName($use_model));
		$class->addProperty('request')
			->setPrivate()
			->addComment('@Inject()')
			->addComment('@var Request')
			->addComment('@desc  Request对象 request->user 可以取当前用户信息');
		$method =$class->addMethod('paginate')
			->addComment('@title 分页')
			->addComment('@return ' . $namespace->unresolveName($use_model))
			->setReturnType($use_model)
			->setReturnNullable()
			->setBody('return $this->model->getList();');
		$method =$class->addMethod('list')
			->addComment('@title 列表')
			->addComment('@return ' . $namespace->unresolveName($use_model))
			->setReturnType($use_model)
			->setReturnNullable()
			->setBody('return $this->model->getList(false);');
		$method =$class->addMethod('info')
			->addComment('@title 详情')
			->addComment('@param int $id 数据主键')
			->addComment('@return ' . $namespace->unresolveName($use_model))
			->setReturnType($use_model)
			->setReturnNullable()
			->setBody('return $this->model->findBy($id);');
		$method->addParameter('id')
			->setType('int');
		$method =$class->addMethod('save')
			->addComment('@title 保存')
			->addComment('@param array $args 待写入数据')
			->addComment('@return bool')
			->setReturnType('bool')
			->setReturnNullable()
			->setBody('return $this->model->storeBy($args);');
		$method->addParameter('args')
			->setType('array');
		$method =$class->addMethod('update')
			->addComment('@title 更新')
			->addComment('@param int $id ID')
			->addComment('@param array $args 待更新的数据')
			->addComment('@return bool')
			->setReturnType('bool')
			->setReturnNullable()
			->setBody('return $this->model->updateBy($id, $args);');
		$method->addParameter('id')
			->setType('int');
		$method->addParameter('args')
			->setType('array');
		$method =$class->addMethod('delete')
			->addComment('@title 删除')
			->addComment('@param int $id ID')
			->addComment('@return bool')
			->setReturnType('bool')
			->setReturnNullable()
			->setBody('return $this->model->deleteBy($id);');
		$method->addParameter('id')
			->setType('int');

		return $file;
	}
}
