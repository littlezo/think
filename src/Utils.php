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

use little\system\model\Config;
use think\facade\Db;
use think\helper\Str;

class Utils
{
    /**
     * 字符串转换成数组.
     *
     * @param string $dep
     */
    public static function stringToArrayBy(string $string, $dep = ','): array
    {
        if (Str::contains($string, $dep)) {
            return explode($dep, trim($string, $dep));
        }

        return [$string];
    }

    /**
     * 搜索参数.
     */
    public static function filterSearchParams(array $params, array $range = []): array
    {
        $search = [];

        // $range = array_merge(['created_time' => ['start_at', 'end_at']], $range);

        if (! empty($range)) {
            foreach ($range as $field => $rangeField) {
                if (count($rangeField) === 1) {
                    $search[$field] = [$params[$rangeField[0]]];
                    unset($params[$rangeField[0]]);
                } else {
                    $search[$field] = [$params[$rangeField[0]], $params[$rangeField[1]]];
                    unset($params[$rangeField[0]], $params[$rangeField[1]]);
                }
            }
        }

        return array_merge($search, $params);
    }

    /**
     * 导入树形数据.
     *
     * @param $data
     * @param $table
     * @param string $pid
     * @param string $primaryKey
     */
    public static function importTreeData($data, $table, $pid = 'parent', $primaryKey = 'id')
    {
        foreach ($data as $value) {
            if (isset($value[$primaryKey])) {
                unset($value[$primaryKey]);
            }

            $children = $value['children'] ?? false;
            if ($children) {
                unset($value['children']);
            }

            // 首先查询是否存在
            $menu = Db::name($table)
                ->where('name', $value['name'])
                ->where('module', $value['module'])
                ->where('mark', $value['mark'])
                ->find();

            if (! empty($menu)) {
                $id = $menu['id'];
            } else {
                $id = Db::name($table)->insertGetId($value);
            }
            if ($children) {
                foreach ($children as &$v) {
                    $v[$pid] = $id;
                    $v['level'] = ! $value[$pid] ? $id : $value['level'] . '-' . $id;
                }
                self::importTreeData($children, $table, $pid);
            }
        }
    }

    /**
     *  解析 Rule 规则.
     *
     * @param $rule
     * @return array
     */
    public static function parseRule($rule)
    {
        [$controller, $action] = explode(Str::contains($rule, '@') ? '@' : '/', $rule);

        $controller = explode('\\', $controller);

        $controllerName = lcfirst(array_pop($controller));

        array_pop($controller);

        $module = array_pop($controller);

        return [$module, $controllerName, $action];
    }

    /**
     * get controller & action.
     *
     * @param $rule
     * @throws \ReflectionException
     * @return false|string[]
     */
    public static function isMethodNeedAuth($rule)
    {
        [$controller, $action] = explode(Str::contains($rule, '@') ? '@' : '/', $rule);

        $docComment = (new \ReflectionClass($controller))->getMethod($action)->getDocComment();

        if (! $docComment) {
            return false;
        }

        return strpos($docComment, config('little.permissions.method_auth_mark')) !== false;
    }

    /**
     * 表前缀
     *
     * @return mixed
     */
    public static function tablePrefix()
    {
        return \config('database.connections.mysql.prefix');
    }

    /**
     * 删除表前缀
     *
     * @return string|string[]
     */
    public static function tableWithoutPrefix(string $table)
    {
        return str_replace(self::tablePrefix(), '', $table);
    }

    /**
     * 添加表前缀
     *
     * @return string
     */
    public static function tableWithPrefix(string $table)
    {
        return Str::contains($table, self::tablePrefix()) ?
                    $table : self::tablePrefix() . $table;
    }

    /**
     * 是否是超级管理员.
     *
     * @return bool
     */
    public static function isSuperApp()
    {
        // return request()->user()->id == config('little.permissions.super_admin_id');
    }

    /**
     * 获取配置.
     *
     * @param $key
     * @return mixed
     */
    public static function config($key)
    {
        // return Config::where('key', $key)->value('value');
    }

    /**
     * public path.
     *
     * @param string $path
     * @return string
     */
    public static function publicPath($path = '')
    {
        return root_path($path ? 'public/' . $path : 'public');
    }

    /**
     * 过滤空字符字段.
     *
     * @param $data
     * @return mixed
     */
    public static function filterEmptyValue($data)
    {
        foreach ($data as $k => $v) {
            if (! $v) {
                unset($data[$k]);
            }
        }
        unset($data->create_time,$data->update_time,$data->delete_time);
        return $data;
    }

    /**
     * 设置 filesystem config.
     */
    public static function setFilesystemConfig()
    {
        $configModel = app(Config::class);

        $upload = $configModel->where('key', 'upload')->find();

        if ($upload) {
            $disk = app()->config->get('filesystem.disks');

            $uploadConfigs = $configModel->getConfig($upload->component);

            if (! empty($uploadConfigs)) {
                // 读取上传可配置数据
                foreach ($uploadConfigs as $key => &$config) {
                    // $disk[$key]['type'] = $key;
                    // 腾讯云配置处理
                    if (strtolower($key) == 'qcloud') {
                        $config['credentials'] = [
                            'appId' => $config['app_id'] ?? '',
                            'secretKey' => $config['secret_key'] ?? '',
                            'secretId' => $config['secret_id'] ?? '',
                        ];
                        $readFromCdn = $config['read_from_cdn'] ?? 0;
                        $config['read_from_cdn'] = intval($readFromCdn) == 1;
                    }
                    // OSS 配置
                    if (strtolower($key) == 'oss') {
                        $isCname = $config['is_cname'] ?? 0;
                        $config['is_cname'] = intval($isCname) == 1;
                    }
                }

                // 合并数组
                array_walk($disk, function (&$item, $key) use ($uploadConfigs) {
                    if (! in_array($key, ['public', 'local'])) {
                        if ($uploadConfigs[$key] ?? false) {
                            foreach ($uploadConfigs[$key] as $k => $value) {
                                $item[$k] = $value;
                            }
                        }
                    }
                });

                $default = Utils::config('site.upload');
                // 重新分配配置
                app()->config->set([
                    'default' => $default ?: 'local',
                    'disks' => $disk,
                ], 'filesystem');
            }
        }
    }
}
