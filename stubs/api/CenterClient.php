<?php

declare(strict_types=1);
/**
 * This file is part of Code Ai.
 *
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @link     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 *
 */
class CenterClient
{
    // 请求地址
    private $request_url = 'http://localhost/api/auth_center/';

    // 平台标识
    private $name = 'littleZov';

    private $app_key = '';

    private $public_key;

    private $private_key;

    private $key_len;

    // 参数
    private $params = [];

    public static function __construct()
    {
        self::initRSA();
        self::params['params'] = [
            'name' => self::name,
            'app_key' => self::app_key,
        ];
    }

    /**
     * POST请求
     * @param unknown $method 请求控制器/方法
     * @param unknown $params 参数
     * @return unknown
     */
    public static function post($method, $params = [])
    {
        self::request_url .= $method;
        $publicEncrypt = self::publicEncrypt(json_encode($params));
        self::params['params']['public_encrypt'] = $publicEncrypt;
        $res = Http::post(self::request_url, self::params);
        if (! empty($res)) {
            //对返回数据进行解密，解密需要一些时间，暂时去掉
//             $decrypted = self::publicDecrypt($res);
//             if(!empty($decrypted)){
//                 $res = json_decode($decrypted, true);
//             }else{
//                 $res = json_decode($res, true);
//             }
            return json_decode($res, true);
        }
        return json_encode(error('接口发生错误'));
    }

    /*
     * 公钥加密
     */
    public static function publicEncrypt($data)
    {
        $encrypted = '';
        $part_len = self::key_len / 8 - 11;
        $parts = str_split($data, $part_len);

        //分段加密
        foreach ($parts as $part) {
            $encrypted_temp = '';
            openssl_public_encrypt($part, $encrypted_temp, self::public_key);
            $encrypted .= $encrypted_temp;
        }

        return self::url_safe_base64_encode($encrypted);
    }

    /*
     * 公钥解密
     */
    public static function publicDecrypt($encrypted)
    {
        $decrypted = '';
        $part_len = self::key_len / 8;
        $base64_decoded = self::url_safe_base64_decode($encrypted);
        $parts = str_split($base64_decoded, $part_len);

        foreach ($parts as $part) {
            $decrypted_temp = '';
            openssl_public_decrypt($part, $decrypted_temp, self::public_key);
            $decrypted .= $decrypted_temp;
        }
        return $decrypted;
    }

    public static function url_safe_base64_encode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    public static function url_safe_base64_decode($data)
    {
        $base_64 = str_replace(['-', '_'], ['+', '/'], $data);
        return base64_decode($base_64);
    }

    /**
     * 初始化公钥 长度.
     */
    private function initRSA()
    {
        $public_key = APP_PATH . '/cert/rsa_public_key.pem';
        $public_key_content = file_get_contents($public_key);
        self::public_key = openssl_pkey_get_public($public_key_content);
        self::key_len = openssl_pkey_get_details(self::public_key)['bits'];
    }
}
