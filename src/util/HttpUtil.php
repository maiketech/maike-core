<?php

namespace maike\util;

/**
 * HTTP请求工具
 * @package maike\util
 */
class HttpUtil
{
    /**
     * header头信息
     * @var string
     */
    private static $headerStr;

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

    public static function put($url, $data = array(), $header = false, $timeout = 10)
    {
        return self::request($url, 'put', $data, $header, $timeout);
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
        self::$headerStr = null;
        $curl = curl_init($url);
        $method = strtoupper($method);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if ($method == 'POST' || $method == 'PUT') curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        if ($header && is_array($header)) curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
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
        self::$headerStr = trim(substr($content, 0, $status['header_size']));
        $content = trim(substr($content, $status['header_size']));
        return ["content" => $content, "status" => $status];
    }


    /**
     * 获取header头字符串
     * @return string
     */
    public static function getHeaderStr()
    {
        return self::$headerStr;
    }

    /**
     * 获取header头数组类型
     * @return array
     */
    public static function getHeader()
    {
        $headArr = explode("\r\n", self::$headerStr);
        return $headArr;
    }
}
