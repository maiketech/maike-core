<?php

namespace maike\services\wechat;

use think\facade\Config;
use EasyWeChat\OfficialAccount\Application;

/**
 * 微信公众号类
 */
class WechatMp
{
    protected static $instance;

    /**
     * @param bool $cache
     * @return Application
     */
    public static function application($cache = false)
    {
        (self::$instance === null || $cache === true) && (self::$instance = new Application(self::config()));
        return self::$instance;
    }

    /**
     * @return array
     */
    public static function config()
    {
        $config = Config::get("wechat.mp");
        return $config;
    }
}
