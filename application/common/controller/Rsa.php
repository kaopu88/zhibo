<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/8/5 0005
 * Time: 下午 5:22
 */

namespace app\common\controller;

class Rsa
{
    /**
     * 获取私钥
     * @return bool|resource
     */
    private static function getPrivateKey()
    {
        $abs_path = dirname($_SERVER['DOCUMENT_ROOT']) . '/rsa_private_key.pem';
        $content = file_get_contents($abs_path);

        return openssl_pkey_get_private($content);
    }

    /**
     * 获取公钥
     * @return bool|resource
     */
    private static function getPublicKey()
    {
        $abs_path = dirname($_SERVER['DOCUMENT_ROOT']) .'/rsa_public_key.pem';
        $content = file_get_contents($abs_path);
        return openssl_pkey_get_public($content);
    }

    protected static function urlsafe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }

    protected static function urlsafe_b64decode($string)
    {
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    /**
     * 私钥加密
     * @param string $data
     * @return null|string
     */
    public static function privEncrypt($data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        $crypto = '';
        foreach (str_split($data, 117) as $chunk) {
            openssl_private_encrypt($chunk, $encrypted, self::getPrivateKey());
            $crypto .= $encrypted;
        }

        $encrypted = self::urlsafe_b64encode($crypto);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        return $encrypted;
    }

    /**
     * 公钥解密
     * @param string $encrypted
     * @return null
     */
    public static function publicDecrypt($encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        $crypto = '';
        foreach (str_split(self::urlsafe_b64decode($encrypted), 128) as $chunk) {
            openssl_public_decrypt($chunk, $decryptData, self::getPublicKey());
            $crypto .= $decryptData;
        }

        return $crypto;
    }

    //公钥加密
    public static function PublicEncrypt($data){
        $crypto = '';
        foreach (str_split($data, 117) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, self::getPublicKey());
            $crypto .= $encryptData;
        }
        $encrypted = self::urlsafe_b64encode($crypto);
        return $encrypted;
    }

    //私钥解密
    public static function PrivateDecrypt($encrypted)
    {
        $crypto = '';
        foreach (str_split(self::urlsafe_b64decode($encrypted), 128) as $chunk) {
            openssl_private_decrypt($chunk, $decryptData, self::getPrivateKey());
            $crypto .= $decryptData;
        }
        return $crypto;
    }
}
