<?php

namespace maike\wechat;

use think\facade\Config;

/**
 * 微信小程序/公众号
 */
class Wechat
{
    public static function __callStatic(string $service, array $config = [])
    {
        if (!$config || count($config) < 1) {
            $config = Config::get("wechat.{$service}");
        }
        return new ("maike\\wechat\\{$service}")($config);
    }
}
