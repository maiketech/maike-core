<?php

namespace maike\services\pay;

use think\facade\Config;
use maike\interfaces\PayInterface;
use maike\utils\Json as JsonUtil;
use maike\utils\Http;
use maike\services\pay\Wechat;

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
        return new ("maike\\services\\pay\\{$service}")($config);
    }
}
