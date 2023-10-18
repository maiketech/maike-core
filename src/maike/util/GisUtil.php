<?php

namespace maike\util;

/**
 * 坐标工具类
 * @package maike\util
 */
class GisUtil
{
    // 圆周率π
    const PI = 3.1415926535897932384626;

    // 火星坐标系与百度坐标系转换的中间量
    const X_PI = 3.14159265358979324 * 3000.0 / 180.0;

    /** 赤道半径(地球长半轴) */
    const SEMI_MAJOR = 6378245.0;

    /** 地球扁率 */
    const FLATTENING = 0.00669342162296594323;

    // WGS84=>GCJ02 地球坐标系=>火星坐标系
    public static function wgs84ToGcj02($lng, $lat)
    {
        if (self::outOfChina($lng, $lat)) {
            return ['lng' => $lng, 'lat' => $lat];
        }
        $offset = self::offset($lng, $lat);
        $mglng = $lng + $offset[0];
        $mglat = $lat + $offset[1];
        return ['lng' => $mglng, 'lat' => $mglat];
    }

    // GCJ02=>WGS84 火星坐标系=>地球坐标系(粗略)
    public static function gcj02ToWgs84($lng, $lat)
    {
        if (self::outOfChina($lng, $lat)) {
            return ['lng' => $lng, 'lat' => $lat];
        }
        $offset = self::offset($lng, $lat);
        $mglng = $lng - $offset[0];
        $mglat = $lat - $offset[1];
        return ['lng' => $mglng, 'lat' => $mglat];
    }

    // GCJ02=>WGS84 火星坐标系=>地球坐标系（精确）
    public static function gcj02ToWgs84Exactly($lng, $lat)
    {
        if (self::outOfChina($lng, $lat)) {
            return ['lng' => $lng, 'lat' => $lat];
        }
        $initDelta = 0.01;
        $threshold = 0.000000001;
        $dLat = $dLon = $initDelta;
        $mLat = $lat - $dLat;
        $mLon = $lng - $dLon;
        $pLat = $lat + $dLat;
        $pLon = $lng + $dLon;
        $wgsLat = $wgsLng = $i = 0;
        while (true) {
            $wgsLat = ($mLat + $pLat) / 2;
            $wgsLng = ($mLon + $pLon) / 2;
            $lnglat = self::wgs84ToGcj02($wgsLng, $wgsLat);
            $dLon = $lnglat["lng"] - $lng;
            $dLat = $lnglat["lat"] - $lat;
            if ((abs($dLat) < $threshold) && (abs($dLon) < $threshold)) break;
            if ($dLat > 0) {
                $pLat = $wgsLat;
            } else {
                $mLat = $wgsLat;
            }
            if ($dLon > 0) {
                $pLon = $wgsLng;
            } else {
                $mLon = $wgsLng;
            }
            if ($i++ > 10000) break;
        }
        return ['lng' => $wgsLng, 'lat' => $wgsLat];
    }

    // GCJ-02=>BD09 火星坐标系=>百度坐标系
    public static function gcj02ToBd09($lng, $lat)
    {
        $z = sqrt($lng * $lng + $lat * $lat) + 0.00002 * sin($lat * self::X_PI);
        $theta = atan2($lat, $lng) + 0.000003 * cos($lng * self::X_PI);
        $bd_lng = $z * cos($theta) + 0.0065;
        $bd_lat = $z * sin($theta) + 0.006;
        return ['lng' => $bd_lng, 'lat' => $bd_lat];
    }

    // BD09=>GCJ-02 百度坐标系=>火星坐标系
    public static function bd09ToGcj02($lng, $lat)
    {
        $x = $lng - 0.0065;
        $y = $lat - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * self::X_PI);
        $theta = atan2($y, $x) - 0.000003 * cos($x * self::X_PI);
        $gcj_lng = $z * cos($theta);
        $gcj_lat = $z * sin($theta);
        return ['lng' => $gcj_lng, 'lat' => $gcj_lat];
    }

    // WGS84=>BD09 地球坐标系=>百度坐标系
    public static function wgs84ToBd09($lng, $lat)
    {
        $lnglat = self::wgs84ToGcj02($lng, $lat);
        return self::gcj02ToBd09($lnglat["lng"], $lnglat["lat"]);
    }

    // BD09=>WGS84 百度坐标系=>地球坐标系
    public static function bd09ToWgs84($lng, $lat)
    {
        $lnglat = self::bd09ToGcj02($lng, $lat);
        return self::gcj02ToWgs84($lnglat["lng"], $lnglat["lat"]);
    }

    /**
     * 判断是否在国内，不在国内不做偏移
     * @param float $lng 坐标经度
     * @param float $lat 坐标纬度
     * @return bool
     */
    public static function outOfChina($lng, $lat)
    {
        return ($lng < 72.004 || $lng > 137.8347) || ($lat < 0.8293 || $lat > 55.8271);
    }

    // 经度偏移量
    private static function transformLng($lng, $lat)
    {
        $ret = 300.0 + $lng + 2.0 * $lat + 0.1 * $lng * $lng + 0.1 * $lng * $lat + 0.1 * sqrt(abs($lng));
        $ret += (20.0 * sin(6.0 * $lng * self::PI) + 20.0 * sin(2.0 * $lng * self::PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($lng * self::PI) + 40.0 * sin($lng / 3.0 * self::PI)) * 2.0 / 3.0;
        $ret += (150.0 * sin($lng / 12.0 * self::PI) + 300.0 * sin($lng / 30.0 * self::PI)) * 2.0 / 3.0;
        return $ret;
    }

    // 纬度偏移量
    private static function transformLat($lng, $lat)
    {
        $ret = -100.0 + 2.0 * $lng + 3.0 * $lat + 0.2 * $lat * $lat + 0.1 * $lng * $lat
            + 0.2 * sqrt(abs($lng));
        $ret += (20.0 * sin(6.0 * $lng * self::PI) + 20.0 * sin(2.0 * $lng * self::PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($lat * self::PI) + 40.0 * sin($lat / 3.0 * self::PI)) * 2.0 / 3.0;
        $ret += (160.0 * sin($lat / 12.0 * self::PI) + 320 * sin($lat * self::PI / 30.0)) * 2.0 / 3.0;
        return $ret;
    }

    // 偏移量
    private static function offset($lng, $lat)
    {
        $lngLat = [0, 0];
        $dlng = self::transformLng($lng - 105.0, $lat - 35.0);
        $dlat = self::transformLat($lng - 105.0, $lat - 35.0);
        $radlat = $lat / 180.0 * self::PI;
        $magic = sin($radlat);
        $magic = 1 - self::FLATTENING * $magic * $magic;
        $sqrtmagic = sqrt($magic);
        $dlng = ($dlng * 180.0) / (self::SEMI_MAJOR / $sqrtmagic * cos($radlat) * self::PI);
        $dlat = ($dlat * 180.0) / ((self::SEMI_MAJOR * (1 - self::FLATTENING)) / ($magic * $sqrtmagic) * self::PI);
        $lngLat[0] = $dlng;
        $lngLat[1] = $dlat;
        return $lngLat;
    }
}
