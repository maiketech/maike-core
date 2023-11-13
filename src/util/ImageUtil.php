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
     * @param  [目录] $path [要保存的路径]
     */
    public static function Base64ToFile($base64, $path)
    {
        //匹配出图片的格式
        // if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64Str, $result)) {
        //     MDir(public_path() . $path);
        //     $type = $result[2];
        //     $new_file = $path . '/' . $fileNamePrefix . time() . ".{$type}";
        //     if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64Str)))) {
        //         return $new_file;
        //     } else {
        //         return false;
        //     }
        // } else {
        //     return false;
        // }
    }

    /**
     * 图片转为Base64
     * 
     * @param string $imagePath 图片文件
     */
    public static function ToBase64($imagePath)
    {
        if (file_exists($imagePath)) {
            $imageInfo = getimagesize($imagePath);
            $imageData = file_get_contents($imagePath);
            if ($imageData) {
                return 'data:' . $imageInfo['mime'] . ';base64,' . chunk_split(base64_encode($imageData));
            }
        }
        return '';
    }
}
