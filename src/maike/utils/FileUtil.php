<?php

namespace maike\utils;

use PhpOffice\PhpWord\IOFactory as PHPWordIOFactory;
use Dompdf\Dompdf;

/**
 * 文件处理类
 * @package maike\utils
 */
class FileUtil
{
    public static function WordToHtml($file)
    {
        //加载docx、doc文件
        $phpWord = PHPWordIOFactory::load($file);
        //转换为html
        $htmlWriter = PHPWordIOFactory::createWriter($phpWord, "HTML");
        return $htmlWriter->getWriterPart('Body')->write();
    }

    public static function HtmlToPdf($html, $filePath = null)
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html,'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("测试PDF");
    }
}
