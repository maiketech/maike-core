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
        //加载docx、doc文件
        $phpWord = PHPWordIOFactory::load($file);
        //转换为html
        $htmlWriter = PHPWordIOFactory::createWriter($phpWord, "HTML");
        return $htmlWriter->getWriterPart('Body')->write();
    }

    public static function WordToPdf($wordTmp, $filePath = null)
    {

    }
}
