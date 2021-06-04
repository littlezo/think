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
use littler\exceptions\LoginFailedException;
use xiaodi\JWTAuth\Facade\Jwt;

class Auth
{
    /**
     * @var mixed
     */
    protected $auth;

    /**
     * @var mixed
     */
    protected $guard;

    // 默认获取
    protected $username = 'username';

    // 校验字段
    protected $password = 'password';

    // 保存用户信息
    protected $user = [];

    /**
     * @var bool
     */
    protected $checkPassword = true;

    public function __construct()
    {
        $this->auth = config('little.auth');
        // dd($this->auth);
        $this->guard = $this->auth['default']['guard'];
        Jwt::store($this->guard);
    }

    /**
     * set guard.
     *
     * @param $guard
     * @return $this
     */
    public function guard($guard)
    {
        $this->guard = $guard;

        return $this;
    }

    /**
     * @param $condition
     * @return mixed
     */
    public function attempt($condition)
    {
        $user = $this->authenticate($condition);
        if (! $user) {
            throw new LoginFailedException();
        }
        if ($user->status == $user::$disable) {
            throw new LoginFailedException('该用户已被禁用|' . $user->username ?? null, Code::USER_FORBIDDEN);
        }
        if ($this->checkPassword && ! password_verify($condition['password'] ?? 'no', $user->password)) {
            throw new LoginFailedException('登录失败,密码错误');
        }
        return $this->{$this->getDriver()}($user);
    }

    /**
     * user.
     *
     * @param null|mixed $token
     * @return mixed
     */
    public function user($token = null)
    {
        $user = $this->user[$this->guard] ?? null;
        $token = $token ?? app()->get('jwt.token')->getToken();
        Jwt::verify($token);
        if (! $user) {
            switch ($this->getDriver()) {
                case 'jwt':
                    $model = app($this->getProvider()['model']);
                    $user = $model->where($model->getAutoPk(), $token->claims()->get($model->getAutoPk()))->find();
                    break;
                default:
                    throw new FailedException('用户不存在');
            }

            $this->user[$this->guard] = $user;

            return $user;
        }

        return $user;
    }

    /**
     * @return mixed
     */
    public function logout()
    {
        Jwt::verify();
        $token = app()->get('jwt.token')->getToken();
        $uid = $token->claims()->get('jti');
        // dd($uid);
        switch ($this->getDriver()) {
            case 'jwt':
                app('jwt.manager')->destroyToken($uid, $this->guard);
                return $uid;
            default:
                throw new FailedException('user not found');
        }
    }

    /**
     * @param $field
     * @return $this
     */
    public function username($field): self
    {
        $this->username = $field;

        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function password($field): self
    {
        $this->password = $field;

        return $this;
    }

    /**
     * 忽略密码认证
     *
     * @return $this
     */
    public function ignorePasswordVerify(): Auth
    {
        $this->checkPassword = false;

        return $this;
    }

    /**
     * @param $user
     * @return string
     */
    protected function jwt($user)
    {
        unset($user->password);
        // dd($user->toArray());
        // Jwt::store($this->guard);
        return Jwt::token($user->{$user->getAutoPk()}, $user->toArray())->toString();
    }

    /**
     * @return string
     */
    protected function jwtKey()
    {
        return $this->guard . '_id';
    }

    /**
     * @return mixed
     */
    protected function getDriver()
    {
        return $this->auth['guards'][$this->guard]['driver'];
    }

    /**
     * @return mixed
     */
    protected function getProvider()
    {
        if (! isset($this->auth['guards'][$this->guard])) {
            throw new FailedException('Auth Guard Not Found');
        }

        return $this->auth['providers'][$this->auth['guards'][$this->guard]['provider']];
    }

    /**
     * @param $condition
     * @return mixed
     */
    protected function authenticate($condition)
    {
        $provider = $this->getProvider();
        return $this->{$provider['driver']}($condition);
    }

    /**
     * @param $condition
     * @return mixed
     */
    protected function orm($condition)
    {
        // dd($this->filter($condition));
        return app($this->getProvider()['model'])->where($this->filter($condition))->find();
    }

    protected function getModel()
    {
        return app($this->getProvider()['model']);
    }

    /**
     * @param $condition
     */
    protected function filter($condition): array
    {
        $where = [];
        $fields = array_keys(app($this->getProvider()['model'])->getFields());
        foreach ($condition as $field => $value) {
            if (in_array($field, $fields) && $field != $this->password) {
                $where[$field] = $value;
            }
        }

        return $where;
    }
}
