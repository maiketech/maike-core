<?php

namespace maike\util;

/**
 * 加解密工具类
 * @package maike\util
 */
class CryptoUtil
{
    private static function getOpenSSLKey($key, $isPublic = true)
    {
        if (empty($key)) return null;
        if (!file_exists($key) && is_string($key)) {
            return $key;
        }
        $key = file_get_contents($key);
        if ($isPublic) {
            $key = openssl_pkey_get_public($key);
        } else {
            $key = openssl_pkey_get_private($key);
        }
    }

    /**
     * RSA加密
     *
     * @param string $data
     * @param string $key
     * @param boolean $isPublic
     * @return string
     */
    public static function RSAEncrypt($data, $key, $isPublic = true)
    {
        $key = self::getOpenSSLKey($key, $isPublic);
        if (!$key || $key == null) return null;
        $crypto = '';
        foreach (str_split($data, 117) as $chunk) {
            if ($isPublic) {
                openssl_public_encrypt($chunk, $encrypted, $key);
            } else {
                openssl_private_encrypt($chunk, $encrypted, $key);
            }
            $crypto .= $encrypted;
        }
        return $crypto ? base64_encode($crypto) : null;
    }

    /**
     * RSA解密
     *
     * @param string $encrypted
     * @param string $key
     * @param boolean $isPublic
     * @return string
     */
    public static function RSADecode($encrypted, $key, $isPublic = true)
    {
        if (!is_string($encrypted) || empty($encrypted)) {
            return null;
        }
        $key = self::getOpenSSLKey($key, $isPublic);
        if (!$key || $key == null) return null;
        $crypto = '';
        foreach (str_split(base64_decode($encrypted), 128) as $chunk) {
            if ($isPublic) {
                openssl_public_decrypt($chunk, $decrypted, $key);
            } else {
                openssl_private_decrypt($chunk, $decrypted, $key);
            }
            $crypto .= $decrypted;
        }
        return $crypto ? $crypto : null;
    }
}
