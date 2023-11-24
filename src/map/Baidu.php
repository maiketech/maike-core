<?php

namespace maike\map;

use maike\trait\ErrorTrait;
use maike\util\HttpUtil;
use maike\util\JsonUtil;
use think\facade\Config;

/**
 * 百度服务器端API
 */
class BaiduMap
{
    use ErrorTrait;

    const SERVER_URL = "https://api.map.baidu.com";
    protected $config = [
        "ak" => ""
    ];

    public function __construct($config = [])
    {
        if (!$config || empty($config)) {
            $config = Config::get("map.baidu");
        }
        $this->config = array_merge($this->config, $config);
    }

    public function PointToAddress($lng, $lat)
    {
        $ak = $this->config['ak'];
        $url = self::SERVER_URL . "/reverse_geocoding/v3/?ak={$ak}&output=json&coordtype=wgs84ll&location={$lat},{$lng}";
        $res = file_get_contents($url);
        if (!empty($res)) {
            $res = JsonUtil::Decode($res);
            return isset($res['result']['formatted_address']) ? $res['result']['formatted_address'] : '';
        }
        return '';
    }
}
