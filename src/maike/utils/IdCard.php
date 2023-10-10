<?php

namespace maike\utils;

/**
 * 身份证帮助类
 * @package maike\utils
 */
class IdCard
{
    /**
     * 根据身份证号码获取性别
     *
     * @param string $idcard
     * @return string
     */
    public static function GetGender($idcard)
    {
        if (empty($idcard)) return null;
        $sexint = (int)substr($idcard, 16, 1);
        return $sexint % 2 === 0 ? '女' : '男';
    }

    /**
     *  根据身份证号码获取生日
     * 
     * @param string $idcard 身份证号码
     * @return string
     */
    public static function GetBirthday($idcard, $separator = '-')
    {
        if (empty($idcard)) return null;
        $bir = substr($idcard, 6, 8);
        $year = (int)substr($bir, 0, 4);
        $month = (int)substr($bir, 4, 2);
        $day = (int)substr($bir, 6, 2);
        return $year . $separator . $month . $separator . $day;
    }

    /**
     *  根据身份证号码计算年龄
     * 
     * @param string $idcard 身份证号码
     * @return int $age
     */
    public static function GetAge($idcard)
    {
        if (empty($idcard)) return null;
        #  获得出生年月日的时间戳
        $date = strtotime(substr($idcard, 6, 8));
        #  获得今日的时间戳
        $today = strtotime('today');
        #  得到两个日期相差的大体年数
        $diff = floor(($today - $date) / 86400 / 365);
        #  strtotime加上这个年数后得到那日的时间戳后与今日的时间戳相比
        $age = strtotime(substr($idcard, 6, 8) . ' +' . $diff . 'years') > $today ? ($diff + 1) : $diff;
        return $age;
    }
}
