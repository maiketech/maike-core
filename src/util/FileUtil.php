<?php

namespace maike\util;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    public static function ExportExcel($data, $filePath = null, $fileName = '')
    {
        if ($data && isset($data['data'])) {
            $data = [$data];
        }
        $spreadsheet = new Spreadsheet();
        //创建sheet
        foreach ($data as $sheetIndex => $item) {
            if ($sheetIndex > 0) $spreadsheet->createSheet();
            $sheet = $spreadsheet->setActiveSheetIndex($sheetIndex);
            $sheet->setTitle($item['title']);
            $sheet->getDefaultColumnDimension()->setWidth(15);
            //表头
            $rowIndex = 1;
            foreach ($item['header'] as $row) {
                $columnIndex = 1;
                foreach ($row as $val) {
                    $sheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $val);
                    $columnIndex++;
                }
                $rowIndex++;
            }
            //数据
            //$rowIndex = count($item['header']);
            foreach ($item['data'] as $row) {
                $columnIndex = 1;
                foreach ($row as $val) {
                    $sheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $val);
                    $columnIndex++;
                }
                $rowIndex++;
            }
        }
        $spreadsheet->setActiveSheetIndex(0);
        // 创建 Excel writer
        $writer = new Xlsx($spreadsheet);
        if ($filePath && !empty($filePath)) {
            //保存为文件
            if ($writer->save($filePath)) {
                return $filePath;
            } else {
                return false;
            }
        } else {
            //输出文件流
            ob_end_clean();
            header("Content-type: text/html; charset=utf-8");
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename=' . urlencode($fileName));
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            //删除清空
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            exit;
        }
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
