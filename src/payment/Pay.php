<?php

namespace maike\payment;

use think\facade\Config;

/**
 * 支付类
 */
class Pay
{
    public static function __callStatic(string $service, array $config = [])
    {
        if (!$config || count($config) < 1) {
            $config = Config::get("pay." . strtolower($service));
        }
        return new ("maike\\payment\\{$service}")($config);
    }
}
