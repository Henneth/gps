<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\DataImport_Model as DataImport_Model;
use DateTime;

class DataImportController extends Controller {

    public function import() {
        $entityBody = file_get_contents('php://input');
    	DataImport_Model::insertRawData($entityBody); // store raw data into a single row

    	$array = json_decode($entityBody, 1);
        $device_id = $array['device_id'];
    	foreach($array['content'] as $item){
    		$command = $item['command'];
    		if ($command =="UD2" || $command =="UD"){
    			$longitude = $item['longitude'];
    			$longitude_logo = $item['longitude_logo'];
    			$longitude_final = (($longitude_logo == 'E') ? 1 : -1) * $longitude;
    			$latitude = $item['latitude'];
    			$latitude_logo = $item['latitude_logo'];
    			$latitude_final = (($latitude_logo == 'N') ? 1 : -1) * $latitude;
                $elevation = $item['elevation'];
    			$date = DateTime::createFromFormat('dmy', $item['date'])->format('Y-m-d');
    			$time = DateTime::createFromFormat('His', $item['time'])->format('H:i:s');
    			DataImport_Model::storeGPSData($device_id, $longitude, $longitude_logo, $longitude_final, $latitude, $latitude_logo, $latitude_final, $elevation, $date, $time);
    		}
    	}
    }

}
