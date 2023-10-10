<?php

namespace maike\utils;

use think\Response;

/**
 * JSON帮助类
 * @package maike\utils
 */
class Json
{
    /**
     * 返回请求成功JSON
     *
     * @param array|null $data
     * @param string|null $msg
     * @return Response
     */
    public static function Success(array $data = null, ?string $msg = 'success'): Response
    {
        return self::Make(10000, $msg, $data, 200);
    }

    /**
     * 返回请求错误JSON
     *
     * @param string $msg
     * @param array|null $data
     * @return Response
     */
    public static function Error($msg = 'error', ?array $data = null): Response
    {
        return self::Make(0, $msg, $data, 200);
    }

    /**
     * 封装think\Response
     *
     * @param integer $code
     * @param string $msg
     * @param array|null $data
     * @param integer|null $statusCode
     * @return Response
     */
    public static function Make(int $code, string $msg, ?array $data = null, ?int $statusCode = 200): Response
    {
        $result = compact('code', 'msg');
        if (!is_null($data)) $result['data'] = $data;
        return Response::create($result, 'json', $statusCode);
    }

    /**
     * Json字符串转为数组或对象
     * 
     * @param string $data Json 字符串
     * @param bool $assoc 是否返回关联数组。默认返回对象
     * @return mixed 成功返回转换后的对象或数组
     */
    public static function Decode(string $data = '', $assoc = true): mixed
    {
        if (empty($data)) {
            return null;
        }
        $data = json_decode($data, $assoc);
        if (($data && is_object($data)) || (is_array($data) && !empty($data))) {
            return $data;
        }
        return null;
    }

    /**
     * 数组或对象转为Json字符串
     * 
     * @param array|object $data 数组或对象
     * @return string 成功返回转换后的的JSON字符串，失败返回空字符串
     */
    public static function Encode($data = []): string
    {
        if (!$data || $data == null || empty($data)) {
            return '';
        }
        if (is_object($data) || is_array($data)) {
            return json_encode($data);
        }
        return '';
    }
}
