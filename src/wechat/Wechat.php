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
        if (empty($service)) return null;
        if (!$config || count($config) < 1) {
            $fk = "wechat." . strtolower($service);
            $config = Config::get($fk);
        }
        return new ("maike\\wechat\\{$service}")($config);
    }
}
