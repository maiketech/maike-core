<?php

declare(strict_types=1);

/**
 * 公共函数库
 */

if (!function_exists('pp')) {
    /**
     * 调试打印
     *
     * @param mixed $data
     */
    function pp(...$vars)
    {
        echo '<pre>';
        foreach ($vars as $data) {
            if (is_array($data) || is_object($data)) {
                print_r($data);
            } else {
                var_dump($data);
            }
        }
        echo '</pre>';
        die("------------ debug end -------------");
    }
}

if (!function_exists('Msectime')) {
    /**
     * 获取毫秒数
     * 
     * @return float
     */
    function Msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }
}

if (!function_exists('throwError')) {
    /**
     * 抛出异常处理
     * 
     * @param string $message
     * @param int $code
     * @param string $exception
     * @throws \maike\exception\ApiException
     */
    function ThrowError($message, $code = 0, $exception = '')
    {
        $e = $exception ?: '\maike\exception\ApiException';
        throw new $e($message, $code);
    }
}

if (!function_exists('BaseUrl')) {
    /**
     * 获取当前域名及根路径
     * 
     * @return string
     */
    function BaseUrl(): string
    {
        static $baseUrl = '';
        if (empty($baseUrl)) {
            $request = \think\facade\Request::instance();
            // url协议，设置强制https或自动获取
            $scheme = $request->scheme();
            $prot = $request->port();
            // 拼接完整url
            $baseUrl = "{$scheme}://" . $request->host()  . (empty($prot) || $prot == '80' ? '' : ':' . $prot) . '/';
        }
        return $baseUrl;
    }
}

if (!function_exists('CreateGuid')) {
    /**
     * 获取全局唯一标识符
     * 
     * @param bool $trim
     * @return string
     */
    function CreateGuid(bool $trim = true): string
    {
        // Windows
        if (function_exists('com_create_guid') === true) {
            $charid = com_create_guid();
            return $trim == true ? trim($charid, '{}') : $charid;
        }
        // OSX/Linux
        if (function_exists('openssl_random_pseudo_bytes') === true) {
            $data = openssl_random_pseudo_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }
        // Fallback (PHP 4.2+)
        mt_srand(intval((float)microtime() * 10000));
        $charid = strtolower(md5(uniqid((string)rand(), true)));
        $hyphen = chr(45);                  // "-"
        $lbrace = $trim ? "" : chr(123);    // "{"
        $rbrace = $trim ? "" : chr(125);    // "}"
        return $lbrace .
            substr($charid, 0, 8) . $hyphen .
            substr($charid, 8, 4) . $hyphen .
            substr($charid, 12, 4) . $hyphen .
            substr($charid, 16, 4) . $hyphen .
            substr($charid, 20, 12) .
            $rbrace;
    }
}

if (!function_exists('CreateSn')) {
    /**
     * 生成唯一编号
     *
     * @param string $prefix 前缀
     * @return string
     */
    function CreateSn($prefix = '')
    {
        return $prefix . date('y') . date('mdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
    }
}

if (!function_exists('FilterEmoji')) {
    /**
     * 过滤emoji表情
     * 
     * @param $text
     * @return string
     */
    function FilterEmoji($text)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $text
        );
        return $str;
    }
}

if (!function_exists('GetNickname')) {
    /**
     * 生成随机昵称
     *
     * @param string $pre 前缀
     * @param integer $len 长度
     * @return string
     */
    function GetNickname($pre = '', $len = 5)
    {
        $microtime = Msectime();
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $nickname = "";
        for ($i = 0; $i < 3; $i++) {
            $nickname .= $chars[mt_rand(0, strlen($chars))];
        }
        return $pre . substr(str_shuffle($nickname . strtoupper(base_convert((string)(time() - 1420070400), 10, 36)) . $microtime), 0, $len);
    }
}

if (!function_exists('CreateDir')) {
    /**
     * 自动生成目录
     */
    function CreateDir($path)
    {
        $path = dirname($path);
        if (is_dir($path)) {
            return $path;
        }
        return is_dir(dirname($path)) || CreateDir(dirname($path)) ? mkdir($path) : false;
    }
}

if (!function_exists('trimAll')) {
    /**
     * 删除所有空格
     *
     * @param string $str
     * @return string
     */
    function TrimAll($str)
    {
        return preg_replace("/\s+/", "", $str);
    }
}
