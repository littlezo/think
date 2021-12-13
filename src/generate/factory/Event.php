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
use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\PhpFile;

class Event extends Factory
{
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

	protected $stubDir;

	public function __construct()
	{
		$this->stubDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR .
			'command' . DIRECTORY_SEPARATOR .
			'stubs' . DIRECTORY_SEPARATOR;
	}

	/**
	 * 事件生成.
	 *
	 * @param $params
	 */
	public function done(array $params): string
	{
		$events = [];
		try {
			// $events += $this->onAfterRead($params);
			// $events += $this->onBeforeInsert($params);
			// $events +=$this->onAfterInsert($params);
			// $events +=$this->onBeforeUpdate($params);
			// $events +=$this->onAfterUpdate($params);
			// $events +=$this->onBeforeWrite($params);
			// $events +=$this->onAfterWrite($params);
			// $events +=$this->onBeforeDelete($params);
			// $events +=$this->onAfterDelete($params);
			// $events +=$this->onBeforeRestore($params);
			// $events +=$this->onAfterRestore($params);
			$events +=$this->onQueryByPoly($params);
			$events +=$this->onSetIncByField($params);
			$events +=$this->onSetDecByField($params);
			$events +=$this->onQueryById($params);
			$events +=$this->onQueryByField($params);
			$events +=$this->onQueryByFind($params);
			$events +=$this->onQueryByList($params);
			$events +=$this->onDeleteById($params);
			$events +=$this->onDeleteByWhere($params);
			$events +=$this->onUpdateById($params);
			$events +=$this->onUpdateByWhere($params);
			$events +=$this->onSave($params);
			$event = include $this->getModulePath($params['event']) . 'config/event.php';
			$event['listen'] = array_merge($event['listen'], $events);
			$dumper = new Dumper();
			$event_content = sprintf('<?php' . PHP_EOL . PHP_EOL . 'return %s;', $dumper->dump($event));

			FileSystem::put($this->getModulePath($params['event']) . 'config/event.php', $event_content);
			return 'success ';
		} catch (\Throwable $exception) {
			throw new \Exception((string) $exception->getTraceAsString());
		}
	}

	/**
	 * 查询后事件.
	 *
	 * @param $params
	 * @return string|string[]
	 */
	protected function onAfterRead($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sReadAfter', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);
		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 查询后事件', $title))
			->addComment(sprintf('@param %s $model %s模型对象', $modelClass, $title))
			->addComment('@return bool true', )
			->setBody(<<<EOF
				// Todo: $title 查询后事件
				return true;
				EOF);
		$method->addParameter('model');
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sReadAfter', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sReadAfter', $className) => sprintf('%s\\On%sReadAfter', $classNamespace, $className)];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sReadAfter', $className) => [sprintf('%s\\On%sReadAfter', $classNamespace, $className)]];
	}

	/**
	 * 新增前事件.
	 *
	 * @param $params
	 */
	protected function onBeforeInsert($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sBeforeInsert', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 新增前事件', $title))
			->addComment(sprintf('@param %s $model %s模型对象', $modelClass, $title))
			->addComment('@return bool true', )
			->setBody(<<<EOF
				// Todo: $title 新增前事件
				return true;
				EOF);
		$method->addParameter('model');
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sBeforeInsert', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sBeforeInsert', $className) => [sprintf('%s\\On%sBeforeInsert', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sBeforeInsert', $className) => [sprintf('%s\\On%sBeforeInsert', $classNamespace, $className)]];
	}

	/**
	 * 新增后事件.
	 *
	 * @param $params
	 */
	protected function onAfterInsert($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sInsertAfter', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 新增后事件', $title))
			->addComment(sprintf('@param %s $model %s模型对象', $modelClass, $title))
			->addComment('@return bool true', )
			->setBody(<<<EOF
				// Todo: $title 新增后事件
				return true;
				EOF);
		$method->addParameter('model');
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sInsertAfter', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sInsertAfter', $className) => [sprintf('%s\\On%sInsertAfter', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sInsertAfter', $className) => [sprintf('%s\\On%sInsertAfter', $classNamespace, $className)]];
	}

	/**
	 * 更新前事件.
	 *
	 * @param $params
	 */
	protected function onBeforeUpdate($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sUpdateBefore', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 更新前事件', $title))
			->addComment(sprintf('@param %s $model %s模型对象', $modelClass, $title))
			->addComment('@return bool true', )
			->setBody(<<<EOF
				// Todo: $title 更新前事件
				return true;
				EOF);
		$method->addParameter('model');
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sUpdateBefore', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sUpdateBefore', $className) => [sprintf('%s\\On%sUpdateBefore', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sUpdateBefore', $className) => [sprintf('%s\\On%sUpdateBefore', $classNamespace, $className)]];
	}

	/**
	 * 更新后事件.
	 *
	 * @param $params
	 */
	protected function onAfterUpdate($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sUpdateAfter', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 更新后事件', $title))
			->addComment(sprintf('@param %s $model %s模型对象', $modelClass, $title))
			->addComment('@return bool true', )
			->setBody(<<<EOF
				// Todo: $title 更新后事件
				return true;
				EOF);
		$method->addParameter('model');
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sUpdateAfter', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sUpdateAfter', $className) => [sprintf('%s\\On%sUpdateAfter', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sUpdateAfter', $className) => [sprintf('%s\\On%sUpdateAfter', $classNamespace, $className)]];
	}

	/**
	 * 写入前事件.
	 *
	 * @param $params
	 */
	protected function onBeforeWrite($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sWriteBefore', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 写入前事件', $title))
			->addComment(sprintf('@param %s $model %s模型对象', $modelClass, $title))
			->addComment('@return bool true', )
			->setBody(<<<EOF
				// Todo: $title 写入前事件
				return true;
				EOF);
		$method->addParameter('model');
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sWriteBefore', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sWriteBefore', $className) => [sprintf('%s\\On%sWriteBefore', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sWriteBefore', $className) => [sprintf('%s\\On%sWriteBefore', $classNamespace, $className)]];
	}

	/**
	 * 写入后事件.
	 *
	 * @param $params
	 */
	protected function onAfterWrite($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sWriteAfter', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 写入后事件', $title))
			->addComment(sprintf('@param %s $model %s模型对象', $modelClass, $title))
			->addComment('@return bool true', )
			->setBody(<<<EOF
				// Todo: $title 写入后事件
				return true;
				EOF);
		$method->addParameter('model');
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sWriteAfter', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sWriteAfter', $className) => [sprintf('%s\\On%sWriteAfter', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sWriteAfter', $className) => [sprintf('%s\\On%sWriteAfter', $classNamespace, $className)]];
	}

	/**
	 * 	删除前事件.
	 *
	 * @param $params
	 */
	protected function onBeforeDelete($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sDeleteBefore', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 删除前事件', $title))
			->addComment(sprintf('@param %s $model %s模型对象', $modelClass, $title))
			->addComment('@return bool true', )
			->setBody(<<<EOF
				// Todo: $title 删除前事件
				return true;
				EOF);
		$method->addParameter('model');
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sDeleteBefore', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sDeleteBefore', $className) => [sprintf('%s\\On%sDeleteBefore', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sDeleteBefore', $className) => [sprintf('%s\\On%sDeleteBefore', $classNamespace, $className)]];
	}

	/**
	 * 删除后事件.
	 *
	 * @param $params
	 */
	protected function onAfterDelete($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sDeleteAfter', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 删除后事件', $title))
			->addComment(sprintf('@param %s $model %s模型对象', $modelClass, $title))
			->addComment('@return bool true', )
			->setBody(<<<EOF
				// Todo: $title 删除后事件
				return true;
				EOF);
		$method->addParameter('model');
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sDeleteAfter', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sDeleteAfter', $className) => [sprintf('%s\\On%sDeleteAfter', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sDeleteAfter', $className) => [sprintf('%s\\On%sDeleteAfter', $classNamespace, $className)]];
	}

	/**
	 * 恢复前事件.
	 *
	 * @param $params
	 */
	protected function onBeforeRestore($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sRestoreBefore', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 恢复前事件', $title))
			->addComment(sprintf('@param %s $model %s模型对象', $modelClass, $title))
			->addComment('@return bool true', )
			->setBody(<<<EOF
				// Todo: $title 恢复前事件
				return true;
				EOF);
		$method->addParameter('model');
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sRestoreBefore', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sRestoreBefore', $className) => [sprintf('%s\\On%sRestoreBefore', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sRestoreBefore', $className) => [sprintf('%s\\On%sRestoreBefore', $classNamespace, $className)]];
	}

	/**
	 * 	恢复后事件.
	 *
	 * @param $params
	 */
	protected function onAfterRestore($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sRestoreAfter', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 恢复后事件', $title))
			->addComment(sprintf('@param %s $model %s模型对象', $modelClass, $title))
			->addComment('@return bool true', )
			->setBody(<<<EOF
				// Todo: $title 恢复后事件
				return true;
				EOF);
		$method->addParameter('model');
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sRestoreAfter', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sRestoreAfter', $className) => [sprintf('%s\\On%sRestoreAfter', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sRestoreAfter', $className) => [sprintf('%s\\On%sRestoreAfter', $classNamespace, $className)]];
	}

	/**
	 * 字段聚合查询.
	 *
	 * @param $params->type count max min avg sum
	 */
	protected function onQueryByPoly($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sReadAfter', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 字段聚合查询', $title))
			->addComment('@param array $args 事件参数')
			->addComment('@param string $args->type 聚合查询类型 支持 count max min avg sum')
			->addComment('@param array $args->condition 查询条件')
			->addComment('@param string $args->field 查询字段')
			->addComment('@return mixed')
			->setBody(<<<EOF
				// Todo: $title 字段聚合查询
				return \$model->where(\$args['condition'])->{\$args['type']}(\$args['field']);
				EOF);

		$method->addParameter('args')
			->setType('array');
		$method->addParameter('model')
			->setType($modelClass);
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sReadAfter', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sReadAfter', $className) => [sprintf('%s\\On%sReadAfter', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sReadAfter', $className) => [sprintf('%s\\On%sReadAfter', $classNamespace, $className)]];
	}

	/**
	 * 字段自增.
	 *
	 * @param $params
	 */
	protected function onSetIncByField($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sSetIncByField', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 字段自增', $title))
			->addComment(sprintf('@param $s $model 事件参数', $modelClass))
			->addComment('@param float $args->sum 数量')
			->addComment('@param array $args->condition 查询条件')
			->addComment('@param string $args->field 自增字段')
			->addComment('@return mixed')
			->setBody(<<<EOF
				// Todo: $title 字段自增
				return \$model->where(\$args['condition'])->setInc(\$args['field'],\$args['sum']);
				EOF);
		$method->addParameter('args')
			->setType('array');
		$method->addParameter('model')
			->setType($modelClass);
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sSetIncByField', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sSetIncByField', $className) => [sprintf('%s\\On%sSetIncByField', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sSetIncByField', $className) => [sprintf('%s\\On%sSetIncByField', $classNamespace, $className)]];
	}

	/**
	 * 字段自减.
	 *
	 * @param $params
	 */
	protected function onSetDecByField($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sSetDecByField', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 字段自减', $title))
			->addComment('@param array $args 事件参数')
			->addComment('@param float $args->sum 数量')
			->addComment('@param array $args->condition 查询条件')
			->addComment('@param string $args->field 自减字段')
			->addComment('@return mixed')
			->setBody(<<<EOF
				// Todo: $title 字段自减
				return \$model->where(\$args['condition'])->setDec(\$args['field'],\$args['sum']);
				EOF);
		$method->addParameter('args')
			->setType('array');
		$method->addParameter('model')
			->setType($modelClass);
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sSetDecByField', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sSetDecByField', $className) => [sprintf('%s\\On%sSetDecByField', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sSetDecByField', $className) => [sprintf('%s\\On%sSetDecByField', $classNamespace, $className)]];
	}

	/**
	 * ID 查询数据.
	 *
	 * @param $params
	 */
	protected function onQueryById($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sQueryById', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s ID 查询数据', $title))
			->addComment('@param int $args ID')
			->addComment('@return mixed')
			->setBody(<<<EOF
				// Todo: $title ID 查询数据
				return \$model->findBy(\$args);
				EOF);

		$method->addParameter('args')
			->setType('int');
		$method->addParameter('model')
			->setType($modelClass);
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sQueryById', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sQueryById', $className) => [sprintf('%s\\On%sQueryById', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sQueryById', $className) => [sprintf('%s\\On%sQueryById', $classNamespace, $className)]];
	}

	/**
	 * 字段值查询.
	 *
	 * @param $params
	 */
	protected function onQueryByField($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sQueryByField', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 查询后事件', $title))
			->addComment('@param array $args 事件参数')
			->addComment('@param array $args->condition 查询条件')
			->addComment('@param string $args->field 获取的字段')
			->addComment('@return mixed')
			->setBody(<<<EOF
				// Todo: $title 字段值查询
				return \$model->where(\$args['condition'])->value(\$args['field']);
				EOF);
		$method->addParameter('args')
			->setType('array');

		$method->addParameter('args')
			->setType('array');
		$method->addParameter('model')
			->setType($modelClass);
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sQueryByField', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sQueryByField', $className) => [sprintf('%s\\On%sQueryByField', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sQueryByField', $className) => [sprintf('%s\\On%sQueryByField', $classNamespace, $className)]];
	}

	/**
	 * 条件查询数据.
	 *
	 * @param $params
	 */
	protected function onQueryByFind($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sQueryByFind', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 条件查询数据', $title))
			->addComment('@param array $args 查询条件')
			->addComment('@return mixed')
			->setBody(<<<EOF
				// Todo: $title 条件查询数据
				 return \$model->where(\$args)->find();
				EOF);

		$method->addParameter('args')
			->setType('array');
		$method->addParameter('model')
			->setType($modelClass);
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sQueryByFind', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sQueryByFind', $className) => [sprintf('%s\\On%sQueryByFind', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sQueryByFind', $className) => [sprintf('%s\\On%sQueryByFind', $classNamespace, $className)]];
	}

	/**
	 * 根据条件 查询数据列表.
	 *
	 * @param $params
	 */
	protected function onQueryByList($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sQueryByList', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 条件 查询数据', $title))
			->addComment('@param array $args 查询条件')
			->addComment('@return mixed')
			->setBody(<<<EOF
				// Todo: $title 条件 查询数据列表
				return \$model->where(\$args)->select();
				EOF);

		$method->addParameter('args')
			->setType('array');
		$method->addParameter('model')
			->setType($modelClass);
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sQueryByList', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sQueryByList', $className) => [sprintf('%s\\On%sQueryByList', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sQueryByList', $className) => [sprintf('%s\\On%sQueryByList', $classNamespace, $className)]];
	}

	/**
	 * 根据id删除数据.
	 *
	 * @param $params
	 */
	protected function onDeleteById($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sDeleteById', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s ID 删除数据.', $title))
			->addComment('@param int $args ID')
			->addComment('@return mixed')
			->setBody(<<<EOF
				// Todo: $title ID 删除数据
				return \$model->deleteBy(\$args);
				EOF);

		$method->addParameter('args')
			->setType('int');
		$method->addParameter('model')
			->setType($modelClass);
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sDeleteById', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sDeleteById', $className) => [sprintf('%s\\On%sDeleteById', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sDeleteById', $className) => [sprintf('%s\\On%sDeleteById', $classNamespace, $className)]];
	}

	/**
	 * 根据条件 删除数据.
	 *
	 * @param $params
	 */
	protected function onDeleteByWhere($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sDeleteByWhere', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 条件 删除数据', $title))
			->addComment('@param array $args 条件')
			->addComment('@return mixed')
			->setBody(<<<EOF
				// Todo: $title 条件 删除数据
				return \$model->where(\$args)->delete();
				EOF);

		$method->addParameter('args')
			->setType('array');
		$method->addParameter('model')
			->setType($modelClass);
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sDeleteByWhere', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sDeleteByWhere', $className) => [sprintf('%s\\On%sDeleteByWhere', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sDeleteByWhere', $className) => [sprintf('%s\\On%sDeleteByWhere', $classNamespace, $className)]];
	}

	/**
	 * 根据ID 更新数据.
	 *
	 * @param $params
	 */
	protected function onUpdateById($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sUpdateById', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s ID 更新数据', $title))
			->addComment('@param array $args 事件参数')
			->addComment('@param int $args->id 数据id')
			->addComment('@param array $args->values 更新的值')
			->addComment('@return mixed')
			->setBody(<<<EOF
				// Todo: $title ID 更新数据
				return \$model->updateBy(\$args['id'],\$args['values']);
				EOF);

		$method->addParameter('args')
			->setType('array');
		$method->addParameter('model')
			->setType($modelClass);
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sUpdateById', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sUpdateById', $className) => [sprintf('%s\\On%sUpdateById', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sUpdateById', $className) => [sprintf('%s\\On%sUpdateById', $classNamespace, $className)]];
	}

	/**
	 * 根据条件 更新数据.
	 *
	 * @param $params
	 */
	protected function onUpdateByWhere($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sUpdateByWhere', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 条件更新数据', $title))
			->addComment('@param array $args 事件参数')
			->addComment('@param int $args->condition 更新条件')
			->addComment('@param array $args->values 更新的值')
			->addComment('@return mixed')
			->setBody(<<<EOF
				// Todo: $title 条件 更新数据
				return \$model->where(\$args['condition'])->save(\$args['values']);
				EOF);

		$method->addParameter('args')
			->setType('array');
		$method->addParameter('model')
			->setType($modelClass);
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sUpdateByWhere', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sUpdateByWhere', $className) => [sprintf('%s\\On%sUpdateByWhere', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sUpdateByWhere', $className) => [sprintf('%s\\On%sUpdateByWhere', $classNamespace, $className)]];
	}

	/**
	 * 写入数据.
	 *
	 * @param $params
	 */
	protected function onSave($params)
	{
		[$className, $classNamespace] = $this->parseFilename($params['event']);
		$modelClass = $params['model'];
		$title = $params['extra']['title'];
		$content = new PhpFile();
		$content->setStrictTypes();
		$content->addComment($this->header);
		$namespace = $content->addNamespace($classNamespace);
		$namespace->addUse($modelClass)->addUse(Inject::class);
		$class = $namespace->addClass(sprintf('On%sSave', $className));
		$class->addProperty('model')
			->setProtected()
			->addComment('@Inject()')
			->addComment('@var ' . $modelClass);

		$method = $class->addMethod('handle')
			->addComment(sprintf('%s 写入数据', $title))
			->addComment('@param array $args 要写入的数据')
			->addComment('@return mixed')
			->setBody(<<<EOF
				// Todo: $title 查询后事件
				return \$model->storeBy(\$args);
				EOF);

		$method->addParameter('args')
			->setType('array');
		$method->addParameter('model')
			->setType($modelClass);
		$eventFile = $this->getGeneratePath(sprintf('%s\\On%sSave', $classNamespace, $className));
		if (file_exists($eventFile)) {
			return [sprintf('On%sSave', $className) => [sprintf('%s\\On%sSave', $classNamespace, $className)]];
		}
		if (! FileSystem::put($eventFile, $content)) {
			throw new FailedException($eventFile . ' generate failed~');
		}
		return [sprintf('On%sSave', $className) => [sprintf('%s\\On%sSave', $classNamespace, $className)]];
	}
}
