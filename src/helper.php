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
use think\Exception;
use think\facade\Event;

/*基础函数*/

/**
 * @method 多维数组转字符串
 * @param type $array
 * @param mixed $arr
 * @return type $string
 */
function multi_array_to_string($arr)
{
    if (is_array($arr)) {
        return implode(',', array_map('multi_array_to_string', $arr));
    }
    return $arr;
}
function array_to_string($arr)
{
    if (is_array($arr)) {
        return implode(',', $arr);
    }
    return $arr;
}
/**
 * @method 多维数组变成一维数组
 * @param type $array
 * @param mixed $result
 * @return type $array
 */
function multi_array_to_array($array, &$result = [])
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            multi_array_to_array($value, $result);
        } else {
            $result[] = $value;
        }
    }
    return $result;
}

/**
 * 把返回的数据集转换成Tree.
 *
 * @param array $list 要转换的数据集
 * @param string $pk
 * @param string $pid parent标记字段
 * @param string $child
 * @param int $root
 * @return array|bool
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
    // 创建Tree
    $tree = [];
    if (! is_array($list)) {
        return false;
    }

    // 创建基于主键的数组引用
    $refer = [];
    foreach ($list as $key => $data) {
        $refer[$data[$pk]] = &$list[$key];
        $refer[$data[$pk]][$child] = [];
        $refer[$data[$pk]]['child_num'] = 0;
    }
    foreach ($refer as $key => $data) {
        // 判断是否存在parent
        $parentId = $data[$pid];
        if ($root == $parentId) {
            $tree[$key] = &$refer[$key];
        } elseif (isset($refer[$parentId])) {
            is_object($refer[$parentId]) && $refer[$parentId] = $refer[$parentId]->toArray();
            $parent = &$refer[$parentId];
            $parent[$child][$key] = &$refer[$key];
            ++$parent['child_num'];
        }
    }
    return $tree;
}

/**
 * 将list_to_tree的树还原成列表.
 * @param array $tree 原来的树
 * @param string $child 孩子节点的键
 * @param string $order 排序显示的键，一般是主键 升序排列
 * @param array $list 过渡用的中间数组，
 * @return array 返回排过序的列表数组
 */
function tree_to_list($tree, $child = '_child', $order = 'id', &$list = [])
{
    if (is_array($tree)) {
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if (isset($reffer[$child])) {
                unset($reffer[$child]);
                tree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }
        $list = list_sort_by($list, $order, $sortby = 'asc');
    }
    return $list;
}

/**
 * 对查询结果集进行排序.
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param string $sortby 排序类型 asc正向排序 desc逆向排序 nat自然排序
 * @return array|bool
 */
function list_sort_by($list, $field, $sortby = 'asc')
{
    if (is_array($list)) {
        $refer = $resultSet = [];
        foreach ($list as $i => $data) {
            $refer[$i] = &$data[$field];
        }
        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc': // 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val) {
            $resultSet[] = &$list[$key];
        }
        return $resultSet;
    }
    return false;
}

/**
 * 对象转化为数组.
 * @param $obj
 * @return array
 */
function object_to_array($obj)
{
    if (is_object($obj)) {
        $obj = (array) $obj;
    }
    if (is_array($obj)) {
        foreach ($obj as $key => $value) {
            $obj[$key] = object_to_array($value);
        }
    }
    return $obj;
}

/**
 * 系统加密方法.
 *
 * @param string $data 要加密的字符串
 * @param string $key 加密密钥
 * @param int $expire 过期时间 单位 秒
 * @return string
 */
function encrypt($data, $key = 'little@zov', $expire = 0)
{
    $key = md5($key);

    $data = base64_encode($data);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = '';

    for ($i = 0; $i < $len; ++$i) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        ++$x;
    }

    $str = sprintf('%010d', $expire ? $expire + time() : 0);

    for ($i = 0; $i < $len; ++$i) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }
    return str_replace([
        '+',
        '/',
        '=',
    ], [
        '-',
        '_',
        '',
    ], base64_encode($str));
}

/**
 * 系统解密方法.
 *
 * @param string $data
 *                     要解密的字符串 （必须是encrypt方法加密的字符串）
 * @param string $key
 *                    加密密钥
 * @return string
 */
function decrypt($data, $key = 'little@zov')
{
    $key = md5($key);
    $data = str_replace([
        '-',
        '_',
    ], [
        '+',
        '/',
    ], $data);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    $data = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data = substr($data, 10);

    if ($expire > 0 && $expire < time()) {
        return '';
    }
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = $str = '';

    for ($i = 0; $i < $len; ++$i) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        ++$x;
    }

    for ($i = 0; $i < $len; ++$i) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 数据签名认证
 * @param $data
 * @return string
 */
function auth_sign($data)
{
    // 数据类型检测
    if (! is_array($data)) {
        $data = (array) $data;
    }
    ksort($data); // 排序
    $code = http_build_query($data); // url编码并生成query字符串
    return sha1($code); // 生成签名
}

/**
 * 重写md5加密方式.
 * @param $str
 * @param mixed $str，salt
 * @param mixed $salt
 * @return string
 */
function salt_md5($str, $salt = 'little@zov')
{
    return $str === '' ? '' : md5(md5($str) . $salt);
}

/**
 * 时间戳转时间.
 * @param $time_stamp
 * @param string $format
 * @return false|string
 */
function time_to_date($time_stamp, $format = 'Y-m-d H:i:s')
{
    if ($time_stamp > 0) {
        $time = date($format, $time_stamp);
    } else {
        $time = '';
    }
    return $time;
}

/**
 * 时间转时间戳.
 * @param $date
 * @return false|int
 */
function date_to_time($date)
{
    return strtotime($date);
}

/**
 * 获取唯一随机字符串.
 * @param int $length
 * @param string $prefix
 * @return string
 */
function unique_random($prefix = 'lz', $length = 8)
{
    $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    str_shuffle($str);
    return $prefix . substr(str_shuffle($str), 0, $length) . date('is');
}

/**
 * 生成随机数.
 * @param int $length
 * @return string
 */
function random_number($length)
{
    $pattern = '1234567890';
    $key = '';
    for ($i = 0; $i < $length; ++$i) {
        $key .= $pattern[
            mt_rand(0, 9)];    //生成php随机数
    }
    return $key;
}

/**
 * 生成随机数.
 * @param int $length
 * @param string $prefix
 * @return string
 */
function random_keys($prefix = null, $length = 8)
{
    $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    $key = '';
    for ($i = 0; $i < $length; ++$i) {
        $key .= $pattern[
            mt_rand(0, 35)];    //生成php随机数
    }
    return $prefix . $key;
}

/**
 * 获取日期订单号.
 * @param int $length
 * @param mixed $prefix
 * @param mixed $random_length
 * @param mixed $date
 * @return string
 */
function time_trade_no($prefix = null, $random_length = 4, $date = 'YmdHis')
{
    $pattern = '1234567890';
    $key = '';
    if ($date) {
        $key = date($date);
    }
    for ($i = 0; $i < $random_length; ++$i) {
        $key .= $pattern[
            mt_rand(0, 9)];    //生成php随机数
    }
    if ($prefix) {
        $key = $prefix . $key;
    }
    return $key;
}
/**
 * 获取会员卡号.
 * @param int $length
 * @param mixed $prefix
 * @param mixed $random_length
 * @param mixed $date
 * @return string
 */
function member_card_no($prefix = null, $date = 'YmdHis', $length = 4)
{
    $pattern = '1234567890';
    $key = '';
    if ($date) {
        $key = date($date);
    }
    for ($i = 0; $i < $length; ++$i) {
        $key .= $pattern[
            mt_rand(0, 9)];    //生成php随机数
    }
    if ($prefix) {
        $key = $prefix . $key;
    }
    return $key;
}
/**
 * 发送HTTP请求方法，目前只支持CURL发送请求
 * @param $url
 * @param int $timeout
 * @param array $header
 * @throws Exception
 * @return bool|string
 */
function curl_http($url, $timeout = 30, $header = [])
{
    if (! function_exists('curl_init')) {
        throw new Exception('server not install curl');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    if (! empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    $data = curl_exec($ch);
    if ($data && is_array(explode("\r\n\r\n", $data))) {
        [$header, $data] = explode("\r\n\r\n", $data);
    } else {
        $header = explode("\r\n\r\n", $data)[0];
        $data = [];
    }
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 301 || $http_code == 302) {
        $matches = [];
        preg_match('/Location:(.*?)\n/', $header, $matches);
        $url = trim(array_pop($matches));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $data = curl_exec($ch);
    }

    if ($data == false) {
        curl_close($ch);
    }
    @curl_close($ch);
    return $data;
}

/**
 * 替换数组元素.
 * @param array $array 数组
 * @param array $replace 替换元素['key' => 'value', 'key' => 'value']
 * @return mixed
 */
function replace_array_element($array, $replace)
{
    foreach ($replace as $k => $v) {
        if ($v == 'unset' || $v == '') {
            unset($array[$k]);
        } else {
            $array[$k] = $v;
        }
    }
    return $array;
}

/*系统函数*/
/**
 * 处理事件.
 * @param string $event 事件名称
 * @param array $args 传入参数
 * @param bool $once 只获取一个有效返回值
 * @return array|mixed|string
 */
function event($event, $args = [], $once = false)
{
    $res = Event::trigger($event, $args);
    if (is_array($res)) {
        $res = array_filter($res);
        sort($res);
    }
    //只返回一个结果集
    if ($once) {
        return $res[0] ?? '';
    }
    return $res;
}

/**
 * 错误返回值函数.
 * @param int $code
 * @param string $message
 * @param string $data
 * @param string $error_code
 * @return array
 */
function error($code = -1, $message = '', $data = '', $error_code = '')
{
    return [
        'code' => $code,
        'message' => $message,
        'data' => $data,
        'error_code' => $error_code,
    ];
}

/**
 * 返回值函数.
 * @param int $code
 * @param string $message
 * @param string $data
 * @return array
 */
function success($code = 0, $message = '', $data = '')
{
    return [
        'code' => $code,
        'message' => $message,
        'data' => $data,
    ];
}

/**
 * 检测命名空间是否存在.
 * @param string $namespace
 */
function namespaceExists($namespace)
{
    $namespace .= '\\';
    foreach (get_declared_classes() as $name) {
        if (strpos($name, $namespace) === 0) {
            return true;
        }
    }
    return false;
}

/**
 * 获取带有表前缀的表名.
 * @param string $table
 * @return string
 */
function table($table = '')
{
    return config('database.connections.prefix') . $table;
}

/**
 * 根据年份计算生肖.
 * @param $year
 * @return mixed
 */
function get_zodiac($year)
{
    $animals = ['鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪'];
    $key = ($year - 1900) % 12;
    return $animals[$key];
}

/**
 * 计算.星座.
 * @param int $month 月份
 * @param int $day 日期
 * @return mixed
 */
function get_constellation($month, $day)
{
    $constellations = ['水瓶座', '双鱼座', '白羊座', '金牛座', '双子座', '巨蟹座', '狮子座', '处女座', '天秤座', '天蝎座', '射手座', '摩羯座'];
    if ($day <= 22) {
        if ($month != 1) {
            $constellation = $constellations[$month - 2];
        } else {
            $constellation = $constellations[11];
        }
    } else {
        $constellation = $constellations[$month - 1];
    }
    return $constellation;
}

/**
 * 数组键名转化为数字.
 * @param $data
 * @param $child_name
 * @return array
 */
function arr_key_to_int($data, $child_name)
{
    $temp_data = array_values($data);
    foreach ($temp_data as $k => $v) {
        if (! empty($v[$child_name])) {
            $temp_data[$k][$child_name] = arr_key_to_int($v[$child_name], $child_name);
        }
    }
    return $temp_data;
}

/**
 * 以天为单位 计算间隔内的日期数组.
 * @param $start_time
 * @param $end_time
 * @param string $format
 * @return array
 */
function period_group($start_time, $end_time, $format = 'Ymd')
{
    $type_time = 3600 * 24;
    $data = [];
    for ($i = $start_time; $i <= $end_time; $i += $type_time) {
        $data[] = date($format, $i);
    }
    return $data;
}

/**
 * 数组删除另一个数组.
 * @param $arr
 * @param $del_arr
 * @return mixed
 */
function arr_del_arr($arr, $del_arr)
{
    foreach ($arr as $k => $v) {
        if (in_array($v, $del_arr)) {
            unset($arr[$k]);
        }
    }
    sort($arr);
    return $arr;
}

/**
 * 检测目录读写权限.
 * @param $dir
 * @return bool
 */
function check_path_is_writable($dir)
{
    $testDir = $dir;
    sp_dir_create($testDir);
    if (is_write($testDir)) {
        return true;
    }
    return false;
}

/**
 * 检查测试文件是否可写入.
 * @param $d
 * @return bool
 */
function check_file_is_writable($d)
{
    $file = '_test.txt';
    $fp = @fopen($d . '/' . $file, 'w');
    if (! $fp) {
        return false;
    }
    fclose($fp);
    $rs = @unlink($d . '/' . $file);
    if ($rs) {
        return true;
    }
    return false;
}

function sp_dir_path($path)
{
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/') {
        $path = $path . '/';
    }
    return $path;
}

/**
 * 检查文件是否创建.
 * @param $path
 * @param int $mode
 * @return bool
 */
function sp_dir_create($path, $mode = 0777)
{
    if (is_dir($path)) {
        return true;
    }
    $ftp_enable = 0;
    $path = sp_dir_path($path);
    $temp = explode('/', $path);
    $cur_dir = '';
    $max = count($temp) - 1;
    for ($i = 0; $i < $max; ++$i) {
        $cur_dir .= $temp[$i] . '/';
        if (@is_dir($cur_dir)) {
            continue;
        }
        @mkdir($cur_dir, 0777, true);
        @chmod($cur_dir, 0777);
    }
    return is_dir($path);
}

/**
 * 判断目录是否为空.
 * @param $dir
 * @return bool
 */
function dir_is_empty($dir)
{
    $handle = opendir($dir);
    while (false !== ($entry = readdir($handle))) {
        if ($entry != '.' && $entry != '..') {
            return false;
        }
    }
    return true;
}

/**
 * 创建文件夹.
 *
 * @param string $path 文件夹路径
 * @param int $mode 访问权限
 * @param bool $recursive 是否递归创建
 * @return bool
 */
function dir_mkdir($path = '', $mode = 0777, $recursive = true)
{
    clearstatcache();
    if (! is_dir($path)) {
        mkdir($path, $mode, $recursive);
        return chmod($path, $mode);
    }
    return true;
}

/**
 * 文件夹文件拷贝.
 *
 * @param string $src 来源文件夹
 * @param string $dst 目的地文件夹
 * @return bool
 */
function dir_copy($src = '', $dst = '')
{
    if (empty($src) || empty($dst)) {
        return false;
    }
    $dir = opendir($src);
    dir_mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                dir_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);

    return true;
}

/**
 * 查询存在目录.
 * @param $dir
 * @return bool
 */
function sp_exist_dir($dir)
{
    $is_exist = false;
    $is_write = false;
    while (! $is_exist) {
        $dir = dirname($dir);
        if (is_dir($dir) || $dir == '.') {
            $is_exist = true;
            if (is_writeable($dir)) {
                $is_write = true;
            }
        }
    }
    return $is_write;
}

/**
 * 拼接字符串.
 * @param $string
 * @param string $delimiter 分割字符
 * @param $value
 * @return string
 */
function string_split($string, $delimiter, $value)
{
    return empty($string) ? $value : $string . $delimiter . $value;
}

/**
 * $str为要进行截取的字符串，$length为截取长度（汉字算一个字，字母算半个字.
 * @param $str
 * @param $length
 * @return string
 */
function str_sub($str, $length = 10)
{
    return mb_substr($str, 0, $length, 'UTF-8') . '...';
}

/**
 * 删除缓存文件使用.
 * @param $dir
 */
function rm_runtime($dir)
{
    $dir = 'runtime/' . $dir;
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != '.' && $file != '..') {
            $full_path = $dir . '/' . $file;
            if (is_dir($full_path)) {
                rm_runtime($full_path);
            } else {
                unlink($full_path);
            }
        }
    }
    closedir($dh);
}

/**
 * 以天为单位 计算间隔内的日期数组.
 * @param $start_time
 * @param $end_time
 * @param mixed $format
 * @return array
 */
function periodGroup($start_time, $end_time, $format = 'Ymd')
{
    $type_time = 3600 * 24;
    $data = [];
    for ($i = $start_time; $i <= $end_time; $i += $type_time) {
        $data[] = date($format, $i);
    }
    return $data;
}

/**
 * 解决个别中文乱码
 * @param $content
 * @param string $to_encoding
 * @param string $from_encoding
 * @return null|false|string|string[]
 */
function mbStrReplace($content, $to_encoding = 'UTF-8', $from_encoding = 'GBK')
{
    $content = mb_convert_encoding($content, $to_encoding, $from_encoding);
    $str = mb_convert_encoding('　', $to_encoding, $from_encoding);
    $content = mb_eregi_replace($str, ' ', $content);
    $content = mb_convert_encoding($content, $from_encoding, $to_encoding);
    return trim($content);
}

/**
 * 将非UTF-8字符集的编码转为UTF-8.
 * @param mixed $mixed 源数据
 * @return mixed utf-8格式数据
 */
function charsetToUTF8($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $k => $v) {
            if (is_array($v)) {
                $mixed[$k] = charsetToUTF8($v);
            } else {
                $encode = mb_detect_encoding($v, ['ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5']);
                if ($encode == 'EUC-CN') {
                    $mixed[$k] = iconv('GBK', 'UTF-8', $v);
                }
            }
        }
    } else {
        $encode = mb_detect_encoding($mixed, ['ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5']);
        if ($encode == 'EUC-CN') {
            $mixed = iconv('GBK', 'UTF-8', $mixed);
        }
    }
    return $mixed;
}

/**
 * 过滤bom.
 * @param $filename
 * @return false|string
 */
function check_bom($filename)
{
    $contents = file_get_contents($filename);
    $charset[1] = substr($contents, 0, 1);
    $charset[2] = substr($contents, 1, 1);
    $charset[3] = substr($contents, 2, 1);
    if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
        return substr($contents, 3);
    }
    return $contents;
}

/**
 * 判断 文件/目录 是否可写（取代系统自带的 is_writeable 函数）.
 *
 * @param string $file 文件/目录
 * @return bool
 */
function is_write($file)
{
    if (is_dir($file)) {
        $dir = $file;
        if ($fp = @fopen("{$dir}/test.txt", 'w')) {
            @fclose($fp);
            @unlink("{$dir}/test.txt");
            $writeable = true;
        } else {
            $writeable = false;
        }
    } else {
        if ($fp = @fopen($file, 'a+')) {
            @fclose($fp);
            $writeable = true;
        } else {
            $writeable = false;
        }
    }
    return $writeable;
}

/**
 * 是否是url链接.
 * @param $string
 * @return bool
 */
function is_url($string)
{
    if (strstr($string, 'http://') === false && strstr($string, 'https://') === false) {
        return false;
    }
    return true;
}

/**
 * 根据两点间的经纬度计算距离.
 * @return number
 */
function getDistance(float $lng1, float $lat1, float $lng2, float $lat2)
{
    if (($lng1 == $lng2) && ($lat1 == $lat2)) {
        return 0;
    }
    //将角度转为狐度
    $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
    $radLat2 = deg2rad($lat2);
    $radLng1 = deg2rad($lng1);
    $radLng2 = deg2rad($lng2);
    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;
    return 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
}

function get_http_type()
{
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
        && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http';
    return $http_type;
}

/**
 * 判断一个坐标是否在一个多边形内（由多个坐标围成的）
 * 基本思想是利用射线法，计算射线与多边形各边的交点，如果是偶数，则点在多边形外，否则
 * 在多边形内。还会考虑一些特殊情况，如点在多边形顶点上，点在多边形边上等特殊情况。
 * @param array $point 指定点坐标  $point=['longitude'=>121.427417,'latitude'=>31.20357];
 * @param array $pts 多边形坐标 顺时针方向
 *                   $arr=[['longitude'=>121.23036,'latitude'=>31.218609],['longitude'=>121.233666,'latitude'=>31.210579].............];
 * @return bool
 */
function is_point_in_polygon($point, $pts)
{
    $N = count($pts);
    $boundOrVertex = true; //如果点位于多边形的顶点或边上，也算做点在多边形内，直接返回true
    $intersectCount = 0; //cross points count of x
    $precision = 2e-10; //浮点类型计算时候与0比较时候的容差
    $p1 = 0; //neighbors bound vertices
    $p2 = 0;
    $p = $point; //测试点

    $p1 = $pts[0]; //left vertex
    for ($i = 1; $i <= $N; ++$i) { //check all rays
        // dump($p1);
        if ($p['longitude'] == $p1['longitude'] && $p['latitude'] == $p1['latitude']) {
            return $boundOrVertex; //p is an vertex
        }

        $p2 = $pts[$i % $N]; //right vertex
        if (
            $p['latitude'] < min($p1['latitude'], $p2['latitude']) || $p['latitude']
            > max($p1['latitude'], $p2['latitude'])
        ) {
            //ray is outside of our interests
            $p1 = $p2;
            continue; //next ray left point
        }

        if (
            $p['latitude'] > min($p1['latitude'], $p2['latitude']) && $p['latitude']
            < max($p1['latitude'], $p2['latitude'])
        ) {
            //ray is crossing over by the algorithm (common part of)
            if ($p['longitude'] <= max($p1['longitude'], $p2['longitude'])) {
                //x is before of ray
                if (
                    $p1['latitude'] == $p2['latitude'] && $p['longitude']
                    >= min($p1['longitude'], $p2['longitude'])
                ) {
                    //overlies on a horizontal ray
                    return $boundOrVertex;
                }

                if ($p1['longitude'] == $p2['longitude']) { //ray is vertical
                    if ($p1['longitude'] == $p['longitude']) { //overlies on a vertical ray
                        return $boundOrVertex;
                    }   //before ray
                    ++$intersectCount;
                } else { //cross point on the left side
                    $xinters = ($p['latitude'] - $p1['latitude']) * ($p2['longitude'] - $p1['longitude'])
                        / ($p2['latitude'] - $p1['latitude']) + $p1['longitude'];
                    //cross point of lng
                    if (abs($p['longitude'] - $xinters) < $precision) {
                        //overlies on a ray
                        return $boundOrVertex;
                    }

                    if ($p['longitude'] < $xinters) { //before ray
                        ++$intersectCount;
                    }
                }
            }
        } else { //special case when ray is crossing through the vertex
            if ($p['latitude'] == $p2['latitude'] && $p['longitude'] <= $p2['longitude']) { //p crossing over p2
                $p3 = $pts[($i + 1) % $N]; //next vertex
                if ($p['latitude'] >= min($p1['latitude'], $p3['latitude']) && $p['latitude'] <= max($p1['latitude'], $p3['latitude'])) { //p.latitude lies between p1.latitude & p3.latitude
                    ++$intersectCount;
                } else {
                    $intersectCount += 2;
                }
            }
        }
        $p1 = $p2; //next ray left point
    }

    if ($intersectCount % 2 == 0) { //偶数在多边形外
        return false;
    }   //奇数在多边形内
    return true;
}

/**
 * 删除指定目录所有文件和目录.
 * @param $path
 */
function deleteDir($path)
{
    if (is_dir($path)) {
        //扫描一个目录内的所有目录和文件并返回数组
        $dirs = scandir($path);
        foreach ($dirs as $dir) {
            //排除目录中的当前目录(.)和上一级目录(..)
            if ($dir != '.' && $dir != '..') {
                //如果是目录则递归子目录，继续操作
                $sonDir = $path . '/' . $dir;
                if (is_dir($sonDir)) {
                    //递归删除
                    deleteDir($sonDir);
                    //目录内的子目录和文件删除后删除空目录
                    @rmdir($sonDir);
                } else {
                    //如果是文件直接删除
                    @unlink($sonDir);
                }
            }
        }
    }
}

/**
 * 复制拷贝.
 * @param string $src 原目录
 * @param string $dst 复制到的目录
 */
function recurseCopy($src, $dst)
{
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurseCopy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

/**
 * 计算两个时间的间隔.
 * @param $begin_time
 * @param $end_time
 * @param mixed $begin_times
 * @param mixed $end_times
 * @param mixed $format
 * @param mixed $type
 * @return int||array
 */
function timeDiff($begin_times = null, $end_times = null, $format = false, $type = '%H:%M:%S')
{
    if (! is_numeric($begin_times)) {
        $begin_times = strtotime((string) $begin_times);
    }
    if (! $begin_times) {
        $begin_times = time();
    }
    if (! is_numeric($end_times)) {
        $end_times = strtotime((string) $end_times);
    }
    if (! $end_times) {
        $end_times = time();
    }

    if ($begin_times < $end_times) {
        $start_time = $begin_times;
        $end_time = $end_times;
    } else {
        $start_time = $end_times;
        $end_time = $begin_times;
    }
    $timeDiff = $end_time - $start_time;
    if (! $format) {
        return $timeDiff;
    }
    $days = intval($timeDiff / 86400);
    $remain = $timeDiff % 86400;
    $hours = intval($remain / 3600);
    $remain = $remain % 3600;
    $mins = intval($remain / 60);
    $secs = $remain % 60;
    $diff_str = '';
    if ($days > 0) {
        $diff_str .= $days . '天';
    }
    if ($hours > 0) {
        $diff_str .= $hours . '时';
    }
    if ($mins > 0) {
        $diff_str .= $mins . '分';
    }
    if ($secs > 0) {
        $diff_str .= $secs . '秒';
    }
    return $diff_str;
}
/**
 * hex 函数操作.
 * @param mixed $hexStr
 * @param mixed $is_hex
 */
function hexScreen($hexStr, $is_hex = true)
{
    $str = '';
    $hexStrArr = [];
    for ($i = 0, $j = 1; $i < strlen($hexStr); $i = $i + 2, $j++) {
        $hexStrArr[] = $hexStr[$i] . $hexStr[$i + 1];
    }
    return $is_hex == true ? implode(' ', $hexStrArr) : implode('', $hexStrArr);
}

function hexToStr($hex)
{
    $str = '';
    for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
        $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));
    }
    return $str;
}

function strToHex($str)
{
    $hex = '';
    for ($i = 0; $i < strlen($str); ++$i) {
        $hex .= dechex(ord($str[$i]));
    }
    return $hex;
}

function isHexString($str)
{
    return ctype_xdigit($str);
}

function is_timestamp($timestamp)
{
    if (strtotime(date('Y-m-d H:i:s', (int) $timestamp)) === (int) $timestamp) {
        return (int) $timestamp;
    }
    return false;
}
