<?php
namespace framework\lib;
class Excel
{

    private $currentSheet;
    private $filePath;
    private $fileType;
    private $sheetIndex = 0;
    private $allColumn;
    private $allRow;
    private $resultData;

    /**
     *
     * @param type $fields = array()
     * @param type $data = array()
     * @param String $filename
     */
    public static function excelExport($fields, $data, $filename = 'excelout')
    {
        new Excel();
        /** Include PHPExcel */
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator("CHUKONG")
            ->setLastModifiedBy("GHUKONG")
            ->setTitle("Order sheet")
            ->setSubject("Order sheet")
            ->setDescription("Order sheet")
            ->setKeywords("Order")
            ->setCategory("Order");

        //for test
        //$objPHPExcel->setActiveSheetIndex(0)
        //    ->setCellValue('A1', 'Hello')
        //    ->setCellValue('B2', 'world!')
        //    ->setCellValue('C1', 'Hello')
        //    ->setCellValue('D2', 'world!');
        //设置表头
        $acsii = 1;
        foreach ($fields as $kf => $struct) {
            $column = self::_excelColumn($acsii);
            $fields[$kf]['excel_column'] = $column;
            if (!empty($struct['typeInt'])) {
                $fields[$kf]['type'] = PHPExcel_Cell_DataType::TYPE_NUMERIC;
            } else {
                $fields[$kf]['type'] = PHPExcel_Cell_DataType::TYPE_STRING;
            }
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($column . '1', $struct['Comment']);
            ++$acsii;
        }//var_dump($fields);exit;
//添加数据
        $rowNum = 2; //从第几行看开始数据
        foreach ($data as $dv) {
            foreach ($fields as $kf => $struct) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($struct['excel_column'] . $rowNum, $dv[$struct['Field']], $struct['type']);
            }
            ++$rowNum;
        }
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
//var_dump($data);exit;
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        ob_clean(); //输出前先就进行out buffer清空
// Redirect output to a client?s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public static function _excelColumn($acsii)
    {
        //debug($acsii.':');
        $column = '';
        $model = 26;
        do {
            //debug($acsii.':');
            $last = ($acsii) % $model;
            //debug('last-'.$last.':');
            if (!$last) {
                $last = 26;
                $acsii = floor($acsii / $model);
            }
            $column = chr($last + 64) . $column;
        } while ($acsii = floor($acsii / $model));
        //debug($column.'<br />');
        return $column;
    }

    //測試
    /* $import=new Excel();
      $import->initialized(dirname(__FILE__) . '/test.xlsx');
      header("Content-type: text/html; charset=utf-8");
      echo '<pre>', print_r($import->fetch()), '</pre>';
     */
    public function __construct()
    {
        require_once DIR_FRAMEWORK . 'lib/PHPExcel/Classes/PHPExcel.php';
        $this->resultData = ResultContent::getInstance();
    }

    /**
     * 获得当前类操作的错误提示信息
     * @return type
     */
    public function getResultData()
    {
        return $this->resultData;
    }

    /**
     *
     * @param type $filePath
     * @return type
     */
    public function initialized($filePath)
    {
        if (!file_exists($filePath)) {
            stop($filePath);
            $this->resultData->setStatus('error');
            $this->resultData->setMessage("The File:{$filePath} is not exists!");
            return $this->resultData;
        }

        $this->filePath = $filePath;

        //以硬盤方式緩存
        $cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_discISAM;

        $cacheSettings = array();

        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        $file_ext = strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));

        switch ($file_ext) {
            case 'csv':
                $this->fileType = 'csv';
                $PHPReader = new PHPExcel_Reader_CSV();

                //默認的輸入字符集
                $PHPReader->setInputEncoding('GBK');

                //默認的分隔符
                $PHPReader->setDelimiter(',');

                if (!$PHPReader->canRead($this->filePath)) {
                    return array();
                }
                $PHPExcel = $PHPReader->load($this->filePath);
                $this->currentSheet = $PHPExcel->getSheet((int)$this->sheetIndex);
                xmp($this->currentSheet);
                // $this->currentSheet = $PHPExcel->getActiveSheet();

                $this->allColumn = $this->currentSheet->getHighestColumn();
                xmp($this->allColumn);
                $this->allRow = $this->currentSheet->getHighestRow();
                stop($this->allRow);
                break;

            case 'xlsx':
                $this->fileType = 'excel';
            //这里不用加break处理因为跟xls的处理方式一样
            case 'xls':
                $this->fileType = 'excel';
                $PHPReader = new PHPExcel_Reader_Excel2007();
                if (!$PHPReader->canRead($this->filePath)) {
                    $PHPReader = new PHPExcel_Reader_Excel5();

                    if (!$PHPReader->canRead($this->filePath)) {
                        return array();
                    }
                }
                $PHPReader->setReadDataOnly(true);
                $PHPExcel = $PHPReader->load($this->filePath);

                $this->currentSheet = $PHPExcel->getSheet((int)$this->sheetIndex);

                //$this->currentSheet = $PHPExcel->getActiveSheet();

                $this->allColumn = $this->currentSheet->getHighestColumn();

                $this->allRow = $this->currentSheet->getHighestRow();
                break;
            default:
                return array();
        }
    }

    public function fetch($beginRow = NULL, $endRow = NULL)
    {
        $currentSheet = $this->currentSheet;

        $allColumn = $this->allColumn;
        $allRow = $this->allRow;

        $dataSrc = $data = array();

        //獲取列標題

        for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
            $val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65, 1)->getValue(); //ord()將字符轉爲十進制數

            $dataSrc[ord($currentColumn) - 65] = strtolower(trim($val));
        }

        //echo implode("\t", $dataSrc);

        $beginRow = $beginRow ? $beginRow : 2;

        $endRow = $endRow ? $endRow : $allRow;

        for ($currentRow = $beginRow; $currentRow <= $endRow; $currentRow++) {

            //從第A列開始輸出$dataRow=array();
            for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {

                $val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65, $currentRow)->getValue(); //ord()將字符轉爲十進制數

                $dataRow[$dataSrc[ord($currentColumn) - 65]] = $val;

                //單元級數據處理 ... 格式化日期等
            }

            //行級數據處理 ...

            if ($dataRow) {
                $data[] = $dataRow;
            }
        }

        //echo '<pre>', print_r($data), '</pre>';
        //echo "\n";
        return $data;
    }

}
