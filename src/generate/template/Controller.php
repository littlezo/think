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

class Controller
{
	use Content;

	/**
	 * use.
	 *
	 * @return string
	 */
	public function uses()
	{
		return <<<'TMP'
			use littler\Request;
			use littler\Response;
			use littler\BaseController;
			{USE}


			TMP;
	}

	/**
	 * construct.
	 *
	 * @param $model
	 * @return string
	 */
	public function construct($model)
	{
		return <<<TMP
			protected \$model;

			    public function __construct({$model} \$model)
			    {
			        \$this->model = \$model;
			    }


			TMP;
	}

	public function createClass($class)
	{
		return <<<TMP
			class {$class} extends Controller
			{
			    {CONTENT}
			}
			TMP;
	}

	/**
	 * list template.
	 *
	 * @return string
	 */
	public function index()
	{
		return <<<TMP
			{$this->controllerFunctionComment('列表', '')}
			    public function index()
			    {
			        return Response::paginate(\$this->model->getList());
			    }


			TMP;
	}

	/**
	 * create template.
	 *
	 * @return string
	 */
	public function create()
	{
		return <<<TMP
			{$this->controllerFunctionComment('单页')}
			    public function create()
			    {
			        //
			    }


			TMP;
	}

	/**
	 * save template.
	 *
	 * @param $createRequest
	 * @return string
	 */
	public function save($createRequest = '')
	{
		$request = $createRequest ? 'CreateRequest' : 'Request';

		return <<<TMP
			{$this->controllerFunctionComment('保存', 'Request ' . $request)}
			    public function save({$request} \$request)
			    {
			        return Response::success(\$this->model->storeBy(\$request->post()));
			    }


			TMP;
	}

	/**
	 * read template.
	 *
	 * @return string
	 */
	public function read()
	{
		return <<<TMP
			{$this->controllerFunctionComment('读取', '$id')}
			    public function read(\$id)
			    {
			       return Response::success(\$this->model->findBy(\$id));
			    }


			TMP;
	}

	/**
	 * edit template.
	 *
	 * @return string
	 */
	public function edit()
	{
		return <<<TMP
			{$this->controllerFunctionComment('编辑', '\$id')}
			    public function edit(\$id)
			    {
			        //
			    }


			TMP;
	}

	/**
	 * update template.
	 *
	 * @param $updateRequest
	 * @return string
	 */
	public function update($updateRequest = '')
	{
		$updateRequest = ($updateRequest ? 'UpdateRequest' : 'Request') . ' $request';

		return <<<TMP
			{$this->controllerFunctionComment('更新', $updateRequest)}
			    public function update({$updateRequest}, \$id)
			    {
			        return Response::success(\$this->model->updateBy(\$id, \$request->post()));
			    }


			TMP;
	}

	/**
	 * delete template.
	 *
	 * @return string
	 */
	public function delete()
	{
		return <<<TMP
			{$this->controllerFunctionComment('删除', '$id')}
			    public function delete(\$id)
			    {
			        return Response::success(\$this->model->deleteBy(\$id));
			    }


			TMP;
	}

	/**
	 * 其他方法.
	 *
	 * @param $function
	 * @param string $method
	 * @return string
	 */
	public function otherFunction($function, $method = 'get')
	{
		$params = $method === 'delete' ? '$id' : 'Request $request';

		return <<<TMP
			{$this->controllerFunctionComment('', $params)}
			    public function {$function}({$params})
			    {
			       // todo
			    }


			TMP;
	}

	/**
	 * 控制器方法注释.
	 *
	 * @param $des
	 * @param $params
	 * @return string
	 */
	protected function controllerFunctionComment($des, $params = '')
	{
		$now = date('Y/m/d H:i', time());

		$params = $params ? '@param ' . $params : '';

		return <<<TMP
			/**
			     * {$des}
			     *
			     * @time {$now}
			     * {$params}
			     * @return \\littler\\Response
			     */
			TMP;
	}
}
