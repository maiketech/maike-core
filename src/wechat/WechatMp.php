<?php

namespace maike\wechat;

use think\facade\Config;

/**
 * 微信公众号类
 */
class WechatMp extends WechatBase
{
    static $instance;

    public static function init($config = [])
    {
        if (self::$instance === null) {
            if (!$config || count($config) < 1) {
                $config = Config::get("wechat.mp");
            }
            self::$instance = new static($config);
        }
        return self::$instance;
    }
}
