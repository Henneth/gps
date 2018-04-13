<?php

require base_path().'/libs/PHPExcel.php';
// require 'libs/PHPExcel/PHPExcel/IOFactory.php';

// function exportAsExcel($filename, $sheets) {
// 	$objPHPExcel = new PHPExcel();
//
// 	// Set properties
// 	$objPHPExcel->getProperties()->setCreator("Race Timing Solutions")
// 		->setLastModifiedBy("Race Timing Solutions");
// 		// ->setTitle("Registrations List")
// 		// ->setSubject("Registrations List")
// 		// ->setDescription("Registrations");
// 		// ->setKeywords("office 2007 openxml php")
// 		// ->setCategory("Test result file");
//
// 	function insertData($colNames, $data, $objPHPExcel){
// 		// Insert column names
// 		$rowNo = 1;
// 		$columnNo = "A";
// 		$styleArray = array(
// 			'borders' => array(
// 				'allborders' => array(
// 					'style' => PHPExcel_Style_Border::BORDER_THIN
// 				)
// 			)
// 		);
// 		foreach ($colNames as $colName){
// 			$objPHPExcel->getActiveSheet()
// 				->setCellValue($columnNo.$rowNo, $colName)
// 				->getStyle($columnNo.$rowNo)->getFont()->setBold(true);
// 			$objPHPExcel->getActiveSheet()
// 				->getStyle($columnNo.$rowNo)->applyFromArray($styleArray);
// 			$objPHPExcel->getActiveSheet()
// 				->getColumnDimension($columnNo)->setAutoSize(true);
// 			$columnNo++;
// 		}
//
// 		// Insert data
// 		$rowNo = 2;
// 		foreach ($data as $row){
// 			$columnNo = "A";
// 			foreach ($row as $cell){
// 				$objPHPExcel->getActiveSheet()
// 					->setCellValue($columnNo.$rowNo, $cell)
// 					->getStyle($columnNo.$rowNo)->applyFromArray($styleArray);
// 					// ->setCellValueByColumnAndRow($columnNo, $rowNo, $cell);
// 				$columnNo++;
// 			}
// 			$rowNo++;
// 		}
// 	}
//
// 	$count = 0;
// 	foreach($sheets as $sheet){
// 		$colNames = $sheet['colNames'];
// 		$data = $sheet['data'];
// 		$sheetname = $sheet['sheetname'];
//
// 	    if ($count > 0){
// 	        $objPHPExcel->createSheet();
// 	        $sheet = $objPHPExcel->setActiveSheetIndex($count);
// 	        $sheet->setTitle($sheetname);
// 			insertData($colNames, $data, $objPHPExcel);
// 	    }else{
// 	        $objPHPExcel->setActiveSheetIndex(0)->setTitle($sheetname);
// 	        insertData($colNames, $data, $objPHPExcel);
// 	    }
// 	    $count++;
// 	}
//
// 	header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
// 	header("Content-Disposition: attachment; filename=\"".$filename.".xlsx\"");
// 	header("Cache-Control: max-age=0");
//
// 	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
// 	// If you want to output e.g. a PDF file, simply do:
// 	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
// 	$objWriter->save('php://output');
// }

function importAsExcel($filename){
	$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
	$cacheSettings = array( ' memoryCacheSize ' => '8MB');
	PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

	$objReader = PHPExcel_IOFactory::createReaderForFile($filename);
	$objReader->setReadDataOnly(true);
	$objPHPExcel = PHPExcel_IOFactory::load($filename);

	$results = array();

	// echo '<table>';

	foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
	    $highestRow         = $worksheet->getHighestRow(); // e.g. 10
	    $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
	    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

	    for ($row = 2; $row <= $highestRow; $row ++) {
	    	// echo '<tr>';
	        for ($col = 0; $col < $highestColumnIndex; $col ++) {
	            $cell = $worksheet->getCellByColumnAndRow($col, $row);
	            // if ($col == 4){
	            // 	if (is_numeric($cell->getValue())) {
	            // 		$val = convertTime($cell->getValue());
	            // 	} else {
	            // 		$val = $cell->getValue();
	            // 	}
	            // } elseif ($col == 8) {
	            // 	$val = PHPExcel_Style_NumberFormat::toFormattedString($cell->getCalculatedValue(), 'yyyy-mm-dd');
	            // } else {
	            	$val = $cell->getValue();
	            // }

	            $results[$row][$col] = $val;
	            // echo '<td>' . $val . '</td>';
	        }
	        // echo '</tr>';
	    }
	    // echo '</table>';
	}


	$results = array_map('array_filter', $results);
    $results = array_filter($results);

    return $results;
}

function convertTime($dec) {

    $seconds = ($dec * 24 * 3600);

    $days = floor($dec);

    $seconds -= $days * 24 * 3600;

    $hours = floor($seconds / 3600);

    $seconds -= $hours * 3600;

    $minutes = floor($seconds / 60);

    $seconds -= $minutes * 60;
    // return the time formatted HH:MM:SS
    return lz($hours).":".lz($minutes).":".lz(intval($seconds));
}

// lz = leading zero
function lz($num) {
    return (strlen($num) < 2) ? "0{$num}" : $num;
}
//
// function importResultsAsExcel($filename){
// 	$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
// 	$cacheSettings = array( ' memoryCacheSize ' => '8MB');
// 	PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
//
// 	$objReader = PHPExcel_IOFactory::createReaderForFile($filename);
// 	$objReader->setReadDataOnly(true);
// 	$objPHPExcel = PHPExcel_IOFactory::load($filename);
//
// 	$results = array();
// 	$arrayOfResults = array();
// 	$colNames = array();
// 	$arrayOfColNames = array();
//
// 	foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
// 	    $highestRow         = $worksheet->getHighestRow(); // e.g. 10
// 	    $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
// 	    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
//
// 	    for ($col = 0; $col < $highestColumnIndex; $col ++) {
// 	        $cell = $worksheet->getCellByColumnAndRow($col, 1);
// 	        $val = $cell->getValue();
// 	        $colNames[$col] = $val;
// 	    }
//
// 	    for ($row = 2; $row <= $highestRow; $row ++) {
// 	    	// echo '<tr>';
// 	        for ($col = 0; $col < $highestColumnIndex; $col ++) {
// 	            $cell = $worksheet->getCellByColumnAndRow($col, $row);
// 	            $val = $cell->getValue();
// 	            $results[$row][$col] = $val;
// 	        }
// 	    }
//     	$results = array_map('array_filter', $results);
// 	    $results = array_filter($results);
// 	    $arrayOfResults[] = $results;
// 	    $arrayOfColNames[] = $colNames;
// 	}
//
//     return array($arrayOfResults, $arrayOfColNames);
// }
//
// function insertAtmidsToExcel($filename, $atmIDs) {
// 	$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
// 	$cacheSettings = array( ' memoryCacheSize ' => '8MB');
// 	PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
//
// 	$objReader = PHPExcel_IOFactory::createReaderForFile($filename);
// 	$objPHPExcel = PHPExcel_IOFactory::load($filename);
//
// 	$objPHPExcel->setActiveSheetIndex(0);
//
// 	$rowNo = 2;
// 	foreach ($atmIDs as $atmID) {
// 		$objPHPExcel->getActiveSheet()->setCellValue('R'.$rowNo, $atmID);
// 		$rowNo++;
// 	}
//
//
// 	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
// 	$filenameArray = explode(".", basename($filename));
// 	$filenameNoExt = array_shift($filenameArray);
// 	$path = 'importedWithATMIDs/'.$filenameNoExt.'.xlsx';
// 	$objWriter->save($path);
//
// 	return $path;
// }
