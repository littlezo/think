<?php

declare(strict_types=1);
/**
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。.
 *
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @link     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 *
 */
namespace littler;

use littler\exceptions\FailedException;
use littler\exceptions\ValidateFailedException;

// 应用请求对象类
class Request extends \think\Request
{
    /**
     * @var bool
     */
    protected $needCreatorId = true;

    /**
     *  批量验证
     *
     * @var bool
     */
    protected $batch = false;

    /**
     * Request constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();
        // $this->filterEmptyField();
        $this->validate();
    }

    /**
     * 过滤空字段.
     *
     * @return $this
     */
    public function filterEmptyField(): Request
    {
        // dd(Utils::filterEmptyValue($this->param));
        if ($this->isGet()) {
            $this->get = Utils::filterEmptyValue($this->get);
        } elseif ($this->isPost()) {
            $this->post = Utils::filterEmptyValue($this->post);
        } elseif ($this->isPut()) {
            $this->put = Utils::filterEmptyValue($this->post);
        } else {
            $this->param = Utils::filterEmptyValue($this->param);
        }
        // dd($this);
        return $this;
    }

    /**
     * 初始化验证
     *
     * @throws \Exception
     * @return mixed
     */
    protected function validate()
    {
        if (method_exists($this, 'rules')) {
            try {
                $validate = app('validate');
                // 批量验证
                if ($this->batch) {
                    $validate->batch($this->batch);
                }

                // 验证
                $message = [];
                if (method_exists($this, 'message')) {
                    $message = $this->message();
                }
                if (! $validate->message(empty($message) ? [] : $message)->check(request()->param(), $this->rules())) {
                    throw new FailedException($validate->getError());
                }
            } catch (\Exception $e) {
                throw new ValidateFailedException($e->getMessage());
            }
        }

        // 设置默认参数
        // if ($this->needCreatorId) {
        //     $this->param['creator_id'] = $this->user()->id;
        // }

        return true;
    }
}
