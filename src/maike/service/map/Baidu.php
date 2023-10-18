<?php

namespace maike\service\map;

use maike\util\HttpUtil;
use maike\util\JsonUtil;
use maike\service\BaseService;

/**
 * 百度服务器端API
 */
class BaiduMap extends BaseService
{
    static $serverUrl = "https://api.map.baidu.com";
    static $ak = "vA7awdbfgdQis6fudKKRUzxbpEgajcGx";

    public static function PointToAddress($lng, $lat)
    {
        $ak = self::$ak;
        $url = self::$serverUrl . "/reverse_geocoding/v3/?ak={$ak}&output=json&coordtype=wgs84ll&location={$lat},{$lng}";
        $res = file_get_contents($url);
        if (!empty($res)) {
            $res = JsonUtil::Decode($res);
            return isset($res['result']['formatted_address']) ? $res['result']['formatted_address'] : '';
        }
        return '';
    }
}
