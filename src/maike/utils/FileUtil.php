<?php

namespace maike\utils;

use PhpOffice\PhpWord\IOFactory as PHPWordIOFactory;

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
}
