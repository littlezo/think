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

use littler\annotation\docs\ApiDocs;
use littler\annotation\Inject;
use littler\annotation\Route;
use littler\annotation\route\Group;
use littler\annotation\route\Middleware;
use littler\annotation\route\Resource;
use littler\annotation\route\Validate;
use littler\BaseController;
use littler\exceptions\FailedException;
use littler\facade\FileSystem;
use littler\JWTAuth\Middleware\Jwt;
use littler\Request;
use littler\Response;
use Nette\PhpGenerator\PhpFile;
use think\helper\Str;

class Controller extends Factory
{
	protected $header = <<<'EOF'
		#logic 做事不讲究逻辑，再努力也只是重复犯错
		## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。
		## 只要思想不滑稽，方法总比苦难多！
		@version 1.0.0
		@author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
		@contact  littlezov@qq.com
		@see     https://github.com/littlezo
		@document https://github.com/littlezo/wiki
		@license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE

		EOF;

	protected $classDocs = <<<TEXT

		    "title": "%s",
		    "version": "1.0.0",
		    "layer": "%s",
		    "module": "%s",
		    "group": "%s",
		    "desc": "查询参数详见快速查询 字段含义参加字段映射"

		TEXT;

	protected $methodDocs = <<<'TEXT'

		    "title": "%s",
		    "version": "v1.0.0",
		    "name": "%s",
		    "headers": {
		        "Authorization": "Bearer Token"
		    },
		    "desc": "查询参数详见快速查询 字段含义参加字段映射",
		    "success": {
		        "code": 200,
		        "type": "success",
		        "message": "成功消息||success",
		        "timestamp": 1234567890,
		        "result": {
		            "encryptData": "加密数据自行解密",
		        },
		    },
		    "error": {
		        "code": 500,
		        "message": "错误消息",
		        "type": "error",
		        "result": "",
		        "timestamp": 1234567890
		    },
		    "param": {
		        %s
		    }

		TEXT;

	protected $pageParam = <<<'TEXT'
		"page": {
		            "required": false,
		            "desc": "页数",
		            "type": "int",
		            "default": 1,
		        },
		        "size": {
		            "required": false,
		            "desc": "单页数量",
		            "type": "int",
		            "default": 10,
		        }
		TEXT;

	/**
	 * @param $params
	 * @return bool|string|string[]
	 */
	public function done(array $params)
	{
		if (! $params['controller']) {
			return false;
		}
		$repository = $this->getTraitContent($params);
		$repositoryFile = $this->getGeneratePath($params['controller_repository']);
		$content = $this->getContent($params);
		$contentPath = $this->getGeneratePath($params['controller']);
		// echo $repository;
		// dd();

		try {
			if (! FileSystem::put($repositoryFile, $repository)) {
				throw new FailedException($params['controller_repository'] . ' generate failed~');
			}
			if (! file_exists($contentPath)) {
				FileSystem::put($contentPath, $content);
			}
			return $contentPath;
			// dd();
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
		[$classNameRoute] = $this->parseFilename($params['controller']);
		[$className, $classNamespace] = $this->parseFilename($params['controller_repository']);
		$use = $params['service'];

		if (! $className) {
			throw new FailedException('未填写控制器名称');
		}

		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);

		$namespace->addUse(Request::class)
			->addUse(Response::class);

		if ($use) {
			$namespace->addUse($use);
		}
		// $namespace->addUse(Jwt::class);
		$class = $namespace->addClass($className)
			->setTrait()
			->addComment('desc 禁止在此写业务逻辑，执行生成后将被覆盖');
		$class->addProperty('service')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $namespace->unresolveName($use));
		$method = $class->addMethod('index')
			->addComment(sprintf('@Route("/%s", method="GET", ignore_verify=false)', Str::snake($classNameRoute)))
			->addComment(sprintf('@apiDocs({%s})', sprintf($this->methodDocs, '分页列表', 'index', $this->pageParam)))
			->addComment('@return \think\Response')
			->setReturnType('think\Response')
			->setReturnNullable()
			->setBody('return Response::paginate($this->service->paginate($request->get()));');
		$method->addParameter('request')
			->setType(Request::class);
		$method = $class->addMethod('info')
			->addComment(sprintf('@Route("/%s/:id", method="GET", ignore_verify=false)', Str::snake($classNameRoute)))
			->addComment(sprintf('@apiDocs({%s})', sprintf($this->methodDocs, '详情', 'info', '')))
			->addComment('@return \think\Response')
			->setReturnType('think\Response')
			->setReturnNullable()
			->setBody('return Response::success($this->service->info($id));');
		$method->addParameter('request')
			->setType(Request::class);
		$method->addParameter('id')
			->setType('int');
		$method = $class->addMethod('save')
			->addComment(sprintf('@Route("/%s", method="POST", ignore_verify=false)', Str::snake($classNameRoute)))
			->addComment(sprintf('@apiDocs({%s})', sprintf($this->methodDocs, '保存', 'save', '')))
			->addComment('@return \think\Response')
			->setReturnType('think\Response')
			->setReturnNullable()
			->setBody('return Response::success($this->service->save($request->post()));');
		$method->addParameter('request')
			->setType(Request::class);
		$method = $class->addMethod('update')
			->addComment(sprintf('@Route("/%s/:id", method="PUT", ignore_verify=false)', Str::snake($classNameRoute)))
			->addComment(sprintf('@apiDocs({%s})', sprintf($this->methodDocs, '更新', 'update', '')))
			->addComment('@return \think\Response')
			->setReturnType('think\Response')
			->setReturnNullable()
			->setBody('return Response::success($this->service->update($id,$request->post()));');
		$method->addParameter('request')
			->setType(Request::class);
		$method->addParameter('id')
			->setType('int');
		$method = $class->addMethod('delete')
			->addComment(sprintf('@Route("/%s/:id", method="DELETE", ignore_verify=false)', Str::snake($classNameRoute)))
			->addComment(sprintf('@apiDocs({%s})', sprintf($this->methodDocs, '删除', 'delete', '')))
			->addComment('@return \think\Response')
			->setReturnType('think\Response')
			->setReturnNullable()
			->setBody('return Response::success($this->service->delete($id));');
		$method->addParameter('request')
			->setType(Request::class);
		$method->addParameter('id')
			->setType('int');
		return $content;
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

		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);

		$namespace->addUse(Request::class)
			->addUse(Inject::class)
			->addUse(Route::class)
			->addUse(Resource::class)
			->addUse(Group::class, 'RouteGroup')
			->addUse(Middleware::class)
			->addUse(Validate::class)
			->addUse(Response::class)
			->addUse(ApiDocs::class)
			->addUse(BaseController::class, 'Controller')
			->addUse($params['controller_repository']);

		if ($use) {
			$namespace->addUse($use);
		}
		$namespace->addUse(Jwt::class);
		// var_dump($params['module']);
		// var_dump($params['module']['title']);
		// dd();
		$class = $namespace->addClass($className)
			->setExtends(BaseController::class)
			->addTrait($params['controller_repository'])
			->addComment(sprintf('Class %s', $className))
			->addComment(sprintf('@package %s', $classNamespace))
			->addComment(sprintf('@RouteGroup("%s")', $params['extra']['layer'] . '/' . $params['extra']['module']))
			// ->addComment(sprintf('@Resource("%s")', $className=='Index' ? $params['extra']['module'] . '/index' : $params['extra']['module']))

			->addComment(sprintf('@Middleware({littler\JWTAuth\Middleware\Jwt::class,"%s"})', $params['extra']['auth']))
			->addComment(sprintf('@apiDocs({%s})', sprintf(
				$this->classDocs,
				$params['extra']['title'],
				$params['extra']['layer'],
				// $params['module']['title'],
				Str::snake($params['extra']['module']),
				Str::snake($className)
			)));
		// ->addComment('desc 禁止在控制器写业务逻辑，执行生成后将被覆盖');
		$class->addProperty('service')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $namespace->unresolveName($use));
		$method = $class->addMethod('list')
			->addComment(sprintf('@Route("/%s/list", method="GET", ignore_verify=false)', Str::snake($className)))
			->addComment(sprintf('@apiDocs({%s})', sprintf($this->methodDocs, '列表无分页', 'list', $this->pageParam)))
			->addComment('@return \think\Response')
			->setReturnType('think\Response')
			->setReturnNullable()
			->setBody('return Response::success($this->service->list($request->get()));');
		$method->addParameter('request')
			->setType(Request::class);
		return $content;
	}
}
