<?php

namespace maike\services;

class Http
{
    /**
     * 模拟GET发起请求
     * @param $url
     * @param array $data
     * @param bool $header
     * @param int $timeout
     * @return bool|string
     */
    public static function get($url, $data = array(), $header = false, $timeout = 10)
    {
        if (!empty($data)) {
            $url .= (stripos($url, '?') === false ? '?' : '&');
            $url .= (is_array($data) ? http_build_query($data) : $data);
        }
        return self::request($url, 'get', array(), $header, $timeout);
    }

    /**
     * 模拟POST发起请求
     * @param $url
     * @param $data
     * @param bool $header
     * @param int $timeout
     * @return bool|string
     */
    public static function post($url, $data = array(), $header = false, $timeout = 10)
    {
        return self::request($url, 'post', $data, $header, $timeout);
    }

    /**
     * curl 请求
     * @param $url
     * @param string $method
     * @param array $data
     * @param bool $header
     * @param int $timeout
     * @return bool|string
     */
    public static function request($url, $method = 'get', $data = array(), $header = false, $timeout = 15)
    {
        $curl = curl_init($url);
        $method = strtoupper($method);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if ($method == 'POST') curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        if ($header !== false) curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        //https请求
        if (1 == strpos("$" . $url, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        list($content, $status) = [curl_exec($curl), curl_getinfo($curl), curl_close($curl)];
        $content = trim(substr($content, $status['header_size']));
        return (intval($status["http_code"]) === 200) ? $content : false;
    }
}
