<?php

namespace maike\util;

/**
 * 数学计算工具类
 * @package maike\util
 */
class MathUtil
{
    /**
     * 两个任意精度数字的减法
     *
     * @param string $num1
     * @param string $num2
     * @param integer $scale 小数位数
     * @return string
     */
    public static function bcsub(string $num1, string $num2, int $scale = 2): string
    {
        return \bcsub($num1, $num2, $scale);
    }

    /**
     * 两个任意精度数字的加法计算
     *
     * @param string $num1
     * @param string $num2
     * @param integer $scale 小数位数
     * @return string
     */
    public static function bcadd($num1, $num2, $scale = 2): string
    {
        return \bcadd($num1, $num2, $scale);
    }

    /**
     * 两个任意精度数字乘法计算
     *
     * @param string $num1
     * @param string $num2
     * @param integer $scale 小数位数
     * @return string
     */
    public static function bcmul($num1, $num2, $scale = 2): string
    {
        return \bcmul($num1, $num2, $scale);
    }

    /**
     * 两个任意精度的数字除法计算
     *
     * @param string $num1
     * @param string $num2
     * @param integer $scale 小数位数
     * @return string
     */
    public static function bcdiv($num1, $num2, int $scale = 2): ?string
    {
        return \bcdiv($num1, $num2, $scale);
    }
}
