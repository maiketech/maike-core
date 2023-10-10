<?php

namespace maike\services\pay;

use think\facade\Config;
use maike\interfaces\PayInterface;
use maike\utils\Json as JsonUtil;
use maike\utils\Http;
use maike\services\pay\Wechat;
use maike\traits\ErrorTrait;

/**
 * 支付基类
 */
class PayBase
{
    use ErrorTrait;

    /**
     * 产生随机字符串
     * 
     * @param int $length 指定字符长度
     * @param string $str 字符串前缀
     * @return string
     */
    public static function createNoncestr($length = 32, $str = "")
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取输入对象
     * 
     * @return false|mixed|string
     */
    public static function getRawInput()
    {
        if (empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            return file_get_contents('php://input');
        } else {
            return $GLOBALS['HTTP_RAW_POST_DATA'];
        }
    }
}
