<?php

namespace maike\util;
/**
 * JSON工具类
 * @package maike\util
 */
class JsonUtil
{

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
