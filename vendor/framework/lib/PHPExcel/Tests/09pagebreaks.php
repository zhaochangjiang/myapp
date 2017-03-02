<?php
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2012 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2012 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.7, 2012-05-19
 */

/** Error reporting */
error_reporting(E_ALL);

date_default_timezone_set('Europe/London');

/** Include PHPExcel */
require_once '../Classes/PHPExcel.php';


// Create new PHPExcel object
echo date('H:i:s'), " Create new PHPExcel object", PHP_EOL;
$objPHPExcel = new PHPExcel();

// Set document properties
echo date('H:i:s'), " Set document properties", PHP_EOL;
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
    ->setLastModifiedBy("Maarten Balliauw")
    ->setTitle("Office 2007 XLSX Test Document")
    ->setSubject("Office 2007 XLSX Test Document")
    ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
    ->setKeywords("office 2007 openxml php")
    ->setCategory("Test result file");


// Create a first sheet
echo date('H:i:s'), " Add data and page breaks", PHP_EOL;
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setCellValue('A1', "Firstname")
    ->setCellValue('B1', "Lastname")
    ->setCellValue('C1', "Phone")
    ->setCellValue('D1', "Fax")
    ->setCellValue('E1', "Is Client ?");


// Add data
for ($i = 2; $i <= 50; $i++) {
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, "FName $i");
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, "LName $i");
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, "PhoneNo $i");
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, "FaxNo $i");
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, true);

    // Add page breaks every 10 rows
    if ($i % 10 == 0) {
        // Add a page break
        $objPHPExcel->getActiveSheet()->setBreak('A' . $i, PHPExcel_Worksheet::BREAK_ROW);
    }
}


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Save Excel 2007 file
echo date('H:i:s'), " Write to Excel2007 format", PHP_EOL;
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
echo date('H:i:s'), " File written to ", str_replace('.php', '.xlsx', __FILE__), PHP_EOL;


// Echo memory peak usage
echo date('H:i:s'), " Peak memory usage: ", (memory_get_peak_usage(true) / 1024 / 1024), " MB", PHP_EOL;

// Echo done
echo date('H:i:s'), " Done writing file", PHP_EOL;
