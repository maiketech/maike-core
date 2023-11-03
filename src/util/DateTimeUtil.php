<?php

namespace maike\util;

/**
 * 日期时间工具类
 * @package maike\util
 */
class DateTimeUtil
{
    /**
     * 日期时间格式化（YmdTHisZ）
     * 
     * @param string $time 日期时间
     * @return string
     */
    public static function ToTZ($time): string
    {
        if ($time == null || empty($time)) return '';
        if (gettype($time) == 'string') {
            $time = date("Ymd#His@", strtotime($time));
        } else {
            $time = date("Ymd#His@", $time);
        }
        return str_replace(["#", "@"], ["T", "Z"], $time);
    }

    /**
     * 时间戳格式化
     * 
     * @param integer $time 时间戳
     * @param string $format 输出格式
     * @return string
     */
    public static function Format(int $time = null, $format = 'Y-m-d H:i:s'): string
    {
        if ($time === null) return date($format, time());
        return intval($time) > 0 ? date($format, $time) : '';
    }

    /**
     * 日期月份增加
     *
     * @param integer $inc
     * @param integer $time
     * @param string $format
     * @return string
     */
    public static function MonthInc($inc = 1, $time = 0, $format = 'Y-m')
    {
        if (!$time || $time == 0) {
            $time = time();
        }
        if (!is_integer($time)) {
            return $time;
        }
        //记录需要计算的当前的天数和具体时间
        $y = intval(date("Y", $time));
        $m = intval(date("m", $time));
        //划算成月份，通过月份首先递增月份
        $nextY = $y;
        $nextM = $m;
        //如果当前月+要加上的月>11 这里之所以用11是因为 js的月份从0开始
        if (($m + $inc) > 11) {
            $nextY = $y + 1;
            $nextM = $m + $inc - 12;
        } else {
            $nextM = $m + $inc;
        }
        $lastDay = date("m", strtotime($nextY . '-' . ($nextM + 1) . '-01'));
        if ($format && !empty($format)) {
            return self::Format(strtotime($nextY . '-' . $nextM . '-' . $lastDay), $format);
        }
        return self::Format(strtotime($nextY . '-' . $nextM . '-' . $lastDay));
    }

    /**
     * 获取近7天日期(含当天)
     *
     * @param string $time
     * @param string $format
     * @return array
     */
    public static function Get7DayDate($time = '', $format = 'Y-m-d')
    {
        $time = $time != '' ? $time : time();
        $date = [];
        for ($i = 0; $i < 7; $i++) {
            $date[$i] = date($format, strtotime('+' . $i - 6 . ' days', $time));
        }
        return $date;
    }

    /**
     * 获取两个日期之间的日期数组
     * @param string $start_time 开始日期
     * @param string $end_time 结束日期
     * @param integer $days 获取多少天内的日期
     */
    public static function GetBetweenDate($start_time, $end_time, $days = 0, $showYear = false, $closeDate = null)
    {
        $start_time = strtotime($start_time);
        if ((!$end_time || is_null($end_time)) && $days > 0) {
            $end_time = strtotime("+" . $days . " day", $start_time);
        } else {
            $end_time = strtotime($end_time);
        }
        $arr = [];
        $hasCloseDate = $closeDate && $closeDate != null && is_array($closeDate) && count($closeDate) > 0;
        while ($start_time <= $end_time) {
            $tempDate = $showYear ? date('Y-m-d', $start_time) : date('m-d', $start_time);
            $start_time = strtotime('+1 day', $start_time);
            if ($hasCloseDate && in_array($tempDate, $closeDate)) {
                $end_time = strtotime("+1 day", $end_time);
            } else {
                $arr[] = $tempDate;
            }
        }
        return $arr;
    }

    //php判断某一天是星期几的方法
    public static function GetWeek($unixTime = '', $prefix = '星期')
    {
        $unixTime = is_numeric($unixTime) ? $unixTime : time();
        $weekarray = array('日', '一', '二', '三', '四', '五', '六');
        return $prefix . $weekarray[date('w', $unixTime)];
    }

    public static function GetAMPM($time)
    {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }
        return str_replace(['AM', 'PM'], ['上午', '下午'], date("A", $time));
    }
}
