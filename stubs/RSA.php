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
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。.
 *
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @see     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 */
class RSA
{
	/**
	 * 生成秘钥.
	 */
	public static function getPrivKey(string $alg = 'sha512', int $bits = 4096)
	{
		$config = [
			'digest_alg' => $alg,
			'private_key_bits' => $bits,
			'private_key_type' => OPENSSL_KEYTYPE_RSA,
		];
		$resources = openssl_pkey_new($config);
		openssl_pkey_export($resources, $private_key, null, $config);
		$public_key = openssl_pkey_get_details($resources);

		if (empty($private_key) || empty($public_key)) {
			new Exception('API_SECRET_KEY_CREATE_ERROR', 900980);
		}
		$data = [
			'public_key' => $public_key['key'],
			'private_key' => $private_key,
		];

		return $data;
	}

	public static function genCert($user_id, $priv_key_passwd)
	{
		// 组织
		$dn = [
			'countryName' => '中国',
			'stateOrProvinceName' => '贵州',
			'localityName' => '贵阳',
			'organizationName' => '莘悦科技',
			'organizationalUnitName' => '莘悦科技',
			'commonName' => 'stye.cn',
			'emailAddress' => 'littlezov@qq.com',
		];
		// 密码  有效期
		$priv_key_days = 365;
		$priv_key = self::getPrivKey();
		$csr = openssl_csr_new($dn, $priv_key);
		$secret = openssl_csr_sign($csr, null, $priv_key, $priv_key_days);
		openssl_x509_export($secret, $publickey);
		openssl_pkey_export($priv_key, $privatekey, $priv_key_passwd);
		openssl_csr_export($csr, $csrStr);
		$fp = fopen("../cert/private/$user_id.key", 'w');
		fwrite($fp, $privatekey);
		fclose($fp);
		$fp = fopen("../cert/public/$user_id.crt", 'w');
		fwrite($fp, $publickey);
		fclose($fp);
	}
}
