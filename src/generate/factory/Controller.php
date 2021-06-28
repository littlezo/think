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

use littler\BaseController;
use littler\exceptions\FailedException;
use littler\facade\FileSystem;
use littler\JWTAuth\Middleware\Jwt;
use littler\Request;
use littler\Response;
use Nette\PhpGenerator\PhpFile;

class Controller extends Factory
{
	protected $methods = [];

	protected $uses = [
		'littler\Request',
		'littler\Response',
		'littler\BaseController',
	];

	protected $header = <<<'EOF'
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
	 * @param $params
	 * @return bool|string|string[]
	 */
	public function done(array $params)
	{
		if (strpos($params['controller'], 'Has')) {
			return false;
		}
		$repository = $this->getTraitContent($params);
		$repositoryFile = $this->getGeneratePath($params['controller_repository']);
		$content = $this->getContent($params);
		$filePath = $this->getGeneratePath($params['controller']);

		try {
			if (! FileSystem::put($repositoryFile, $repository)) {
				throw new FailedException($params['controller_repository'] . ' generate failed~');
			}
			// echo $content;
			// dd();
			// if (! file_exists($path)) {
			FileSystem::put($filePath, $content);
			// }
			return $filePath;
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
	public function getTraitContent($params)
	{
		if (! $params['service']) {
			throw new FailedException('params has lost～');
		}
		[$className, $classNamespace] = $this->parseFilename($params['controller_repository']);
		$use = $params['service'];

		if (! $className) {
			throw new FailedException('未填写控制器名称');
		}

		$file = new PhpFile();
		$file->setStrictTypes();
		$file->addComment($this->header);
		$namespace = $file->addNamespace($classNamespace);

		$namespace->addUse(Request::class)
			->addUse(Response::class);

		if ($use) {
			$namespace->addUse($use);
		}
		$namespace->addUse(Jwt::class);
		$class = $namespace->addClass($className)
			->setTrait()
			->addComment('@desc 禁止在此写业务逻辑，执行生成后将被覆盖');
		$class->addProperty('service')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $namespace->unresolveName($use));
		$method = $class->addMethod('index')
			->addComment('@title 分页列表')
			->addComment('@Route("index", method="GET")')
			->addComment('@return \think\Response')
			->addComment('@desc 其他参数详见快速查询 与字段映射')
			->setReturnType('think\Response')
			->setReturnNullable()
			->setBody('return Response::paginate($this->service->paginate($request->get()));');
		$method->addParameter('request')
			->setType(Request::class);
		$method = $class->addMethod('read')
			->addComment('@title 详情')
			->addComment('@Route("read/:id", method="GET")')
			->addComment('@param int $id 主键id')
			->addComment('@return \think\Response')
			->addComment('@desc 其他参数详见快速查询 与字段映射')
			->setReturnType('think\Response')
			->setReturnNullable()
			->setBody('return Response::success($this->service->info($id));');
		$method->addParameter('request')
			->setType(Request::class);
		$method->addParameter('id')
			->setType('int');
		$method = $class->addMethod('save')
			->addComment('@title 列表')
			->addComment('@Route("save", method="POST")')
			->addComment('@param array $args 待写入数据')
			->addComment('@return \think\Response')
			->addComment('@desc 其他参数详见快速查询 与字段映射')
			->setReturnType('think\Response')
			->setReturnNullable()
			->setBody('return Response::success($this->service->save($request->post()));');
		$method->addParameter('request')
			->setType(Request::class);
		$method = $class->addMethod('update')
			->addComment('@title 更新')
			->addComment('@Route("update/:id", method="PUT")')
			->addComment('@param int $id 主键ID')
			->addComment('@param array $args 待更新的数据')
			->addComment('@return \think\Response')
			->addComment('@desc 其他参数详见快速查询 与字段映射')
			->setReturnType('think\Response')
			->setReturnNullable()
			->setBody('return Response::success($this->service->update($id,$request->post()));');
		$method->addParameter('request')
			->setType(Request::class);
		$method->addParameter('id')
			->setType('int');
		$method = $class->addMethod('delete')
			->addComment('@title 删除')
			->addComment('@param int $id 要删除的数据ID')
			->addComment('@Route("delete/:id", method="DELETE")')
			->addComment('@return \think\Response')
			->addComment('@desc 其他参数详见快速查询 与字段映射')
			->setReturnType('think\Response')
			->setReturnNullable()
			->setBody('return Response::success($this->service->delete($id));');
		$method->addParameter('request')
			->setType(Request::class);
		$method->addParameter('id')
			->setType('int');
		return $file;
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
		[$className, $classNamespace] = $this->parseFilename($params['controller']);
		$use = $params['service'];

		if (! $className) {
			throw new FailedException('未填写控制器名称');
		}

		$file = new PhpFile();
		$file->setStrictTypes();
		$file->addComment($this->header);
		$namespace = $file->addNamespace($classNamespace);

		$namespace->addUse(Request::class)
			->addUse(Response::class)
			->addUse(BaseController::class, 'Controller')
			->addUse($params['controller_repository']);

		if ($use) {
			$namespace->addUse($use);
		}
		$namespace->addUse(Jwt::class);
		$class = $namespace->addClass($className)
			->setExtends(BaseController::class)
			->addTrait($params['controller_repository'])
			->addComment(sprintf('@title("%s")', $params['extra']['title']))
			->addComment(sprintf('@Class %s', $className))
			->addComment(sprintf('@package %s', $classNamespace))
			->addComment(sprintf('@Group("%s")', $params['extra']['layer']))
			->addComment(sprintf('@Resource("%s")', $params['extra']['module']))
			->addComment(sprintf('@Middleware({littler\JWTAuth\Middleware\Jwt::class,"%s"})', $params['extra']['auth']))
			->addComment('@desc 禁止在控制器写业务逻辑，执行生成后将被覆盖');
		$class->addProperty('service')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $namespace->unresolveName($use));
		$method = $class->addMethod('list')
			->addComment('@title 非分页列表')
			->addComment('@Route("list", method="GET")')
			->addComment('@return \think\Response')
			->addComment('@desc 其他参数详见快速查询 与字段映射')
			->setReturnType('think\Response')
			->setReturnNullable()
			->setBody('return Response::success($this->service->list($request->get()));');
		$method->addParameter('request')
			->setType(Request::class);
		return $file;
	}
}
