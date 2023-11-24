<?php

namespace maike\util;

/**
 * 图片处理工具类
 * @package maike\util
 */
class ImageUtil
{
    /**
     * Base64图片转本地图片并保存
     * 
     * @param string $base64
     * @param string $filePath [要保存的路径]
     */
    public static function Base64ToFile($base64, $filePath)
    {
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
            $path = CreateDir($filePath);
            if (is_file($filePath)) {
                $type = $result[2]; //文件类型
                $filePath = $path . '/' . md5(time()) . ".{$type}";
            }
            if (file_put_contents($filePath, base64_decode(str_replace($result[1], '', $base64)))) {
                return $filePath;
            }
        }
        return false;
    }

    /**
     * 图片转为Base64
     * 
     * @param string $filePath 图片文件
     */
    public static function FileToBase64($filePath)
    {
        if (file_exists($filePath)) {
            $imageInfo = getimagesize($filePath);
            $imageData = file_get_contents($filePath);
            if ($imageData) {
                return 'data:' . $imageInfo['mime'] . ';base64,' . base64_encode($imageData);
            }
        }
        return '';
    }
}
