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

/**
 * This file is part of Code Ai.
 *
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @see     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 */
class Signature
{
	private $public_key;

	private $private_key;

	private $key_len;

	public function __construct()
	{
		self::initRSA();
	}

	/**
	 * 公钥加密.
	 */
	public static function publicEncrypt($data)
	{
		$encrypted = '';
		$part_len = self::$key_len / 8 - 11;
		$parts = str_split($data, $part_len);

		//分段加密
		foreach ($parts as $part) {
			$encrypted_temp = '';
			openssl_public_encrypt($part, $encrypted_temp, self::$public_key);
			$encrypted .= $encrypted_temp;
		}

		return self::safe_base64_encode($encrypted);
	}

	/**
	 * 公钥解密.
	 */
	public static function publicDecrypt($encrypted)
	{
		$decrypted = '';
		$part_len = self::$key_len / 8;
		$base64_decoded = self::safe_base64_decode($encrypted);
		$parts = str_split($base64_decoded, $part_len);

		foreach ($parts as $part) {
			$decrypted_temp = '';
			openssl_public_decrypt($part, $decrypted_temp, self::$public_key);
			$decrypted .= $decrypted_temp;
		}
		return $decrypted;
	}

	/**
	 * 私钥解密.
	 * @param string $encrypted
	 * @param string $private_key
	 * @param mixed $public_key
	 * @return array|string $decrypted
	 */
	public static function privateDecrypt($encrypted, $private_key, $public_key)
	{
		$private_check = openssl_pkey_get_private($private_key);
		if (! $private_check) {
			return error(-1, 'PRIVATE_KEY_ERROR');
		}
		$public_check = openssl_pkey_get_public($public_key);
		if (! $public_check) {
			return error(-1, 'PUBLIC_KEY_ERROR');
		}

		$details = openssl_pkey_get_details($public_check);
		$bits = $details['bits'];

		$decrypted = '';
		$base64_decoded = self::safe_base64_decode($encrypted);
		// 分段解密
		$parts = str_split($base64_decoded, ($bits / 8));
		foreach ($parts as $part) {
			$decrypted_temp = '';
			$decrypt_res = openssl_private_decrypt($part, $decrypted_temp, $private_key);
			if (! $decrypt_res) {
				return error(-1, 'DECRYPT_FAIL');
			}
			$decrypted .= $decrypted_temp;
		}

		return $decrypted;
	}

	/**
	 * base64编码
	 * @param array|string $string
	 */
	public static function safe_base64_encode($data)
	{
		return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
	}

	/**
	 * base64解码
	 * @param array|string $string
	 */
	public static function safe_base64_decode($string)
	{
		$base_64 = str_replace(['-', '_'], ['+', '/'], $string);
		return base64_decode($base_64, true);
	}

	/**
	 * 初始化公钥 长度.
	 */
	private static function initRSA()
	{
		$public_key = root_path() . '/cert/rsa_public_key.pem';
		$public_key_content = file_get_contents($public_key);
		self::$public_key = openssl_pkey_get_public($public_key_content);
		self::$key_len = openssl_pkey_get_details(self::$public_key)['bits'];
	}
}
