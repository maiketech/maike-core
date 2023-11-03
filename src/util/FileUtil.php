<?php

namespace maike\util;

use PhpOffice\PhpWord\IOFactory as PHPWordIOFactory;

/**
 * 文件工具类
 * @package maike\util
 */
class FileUtil
{
    public static function WordToHtml($file)
    {
    }

    public static function WordToPdf($wordTmp, $filePath = null)
    {
    }

    /**
     * 下载远程文件
     *
     * @param string $url 文件图片的URL
     * @param string $path 保存路径
     * @return void
     */
    function DownloadFile($url, $path = 'donw')
    {
        try {
            $saveFile = $path . "/" . date("YmdHis") . "/" . md5(date("YmdHis") . time()) . "." . substr(strrchr($url, "."), 1);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //是否抓取跳转后的页面
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, $url);
            ob_start();
            curl_exec($ch);
            $return_content = ob_get_contents();
            ob_end_clean();
            $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $fp = @fopen(public_path() . $saveFile, 'a');
            $res = fwrite($fp, $return_content);
            return $res ? $saveFile : false;
        } catch (\Exception $e) {
            \think\facade\Log::write($e->getMessage(), "error");
            return false;
        }
    }
}
