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
use littler\traits\DeleteTrait;
use littler\traits\InfoTrait;
use littler\traits\LayoutTrait;
use littler\traits\ListTrait;
use littler\traits\PageTrait;
use littler\traits\SaveTrait;
use littler\traits\UpdateTrait;
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
		    "name": "%s",
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
		$content = $this->getContent($params);
		$contentPath = $this->getGeneratePath($params['controller']);
		// echo $content;
		// dd($params);

		try {
			if (! in_array($params['table'], ['user_account', 'user_access'], true)) {
				FileSystem::put($contentPath, $content);
			}
			return $contentPath;
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
		$is_layout = $params['extra']['is_layout'];

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
			->addUse(LayoutTrait::class)
			->addUse(PageTrait::class)
			->addUse(ListTrait::class)
			->addUse(SaveTrait::class)
			->addUse(InfoTrait::class)
			->addUse(UpdateTrait::class)
			->addUse(DeleteTrait::class);

		if ($use) {
			$namespace->addUse($use);
		}
		$namespace->addUse(Jwt::class);
		$namespace->addClass($className)
			->setExtends(BaseController::class);
		$class = $namespace->addClass($className)
			->setExtends(BaseController::class)
			->addComment(sprintf('Class %s', $className))
			->addComment(sprintf('@package %s', $classNamespace))
			->addComment(sprintf('@RouteGroup("%s")', $params['extra']['layer'] . '/' . $params['extra']['module'] . '/' . Str::snake($className)))
			->addComment(sprintf('@Middleware({littler\JWTAuth\Middleware\Jwt::class,"%s"})', $params['extra']['auth']))
			->addComment(sprintf('@apiDocs({%s})', sprintf(
				$this->classDocs,
				$params['extra']['title'],
				$params['extra']['layer'],
				Str::snake($className),
				Str::snake($params['extra']['module']),
				Str::snake($className)
			)));

		if ($is_layout) {
			$class->addTrait(LayoutTrait::class);
		}
		$class->addTrait(PageTrait::class)
		->addTrait(InfoTrait::class)
			->addTrait(SaveTrait::class)
			->addTrait(UpdateTrait::class)
			->addTrait(DeleteTrait::class);
		$class->addProperty('service')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $namespace->unresolveName($use));

		// echo $content;
		return $content;
	}
}
