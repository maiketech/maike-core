<?php

namespace maike\sms;

use think\facade\Config;

/**
 * 短信发送类
 */
class Sms
{
    public static function __callStatic(string $service, array $config = [])
    {
        if (empty($service)) return null;
        if (!$config || count($config) < 1) {
            $config = Config::get("sms");
        }
        return new ("maike\\sms\\{$service}")($config);
    }
}
