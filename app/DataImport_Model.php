<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class DataImport_Model extends Model
{
	public static function storeGPSData($device_id, $longitude, $longitude_logo, $longitude_final, $latitude, $latitude_logo, $latitude_final, $date, $time) {
		DB::insert("INSERT INTO gps_data
            (`device_id`, `longitude`,`longitude_logo`, `longitude_final`,`latitude`,`latitude_logo`, `latitude_final`, `datetime`)
            VALUES (:device_id, :longitude, :longitude_logo, :longitude_final, :latitude, :latitude_logo, :latitude_final, :datetime)", [
                "device_id"=>$device_id,
                "longitude"=>$longitude,
                "longitude_logo"=>$longitude_logo,
                "longitude_final"=>$longitude_final,
                "latitude"=>$latitude,
                "latitude_logo"=>$latitude_logo,
                "latitude_final"=>$latitude_final,
                "datetime"=>$date.' '.$time
            ]);
	}
	public static function insertRawData($json) {
		DB::insert("INSERT INTO gps_raw (`raw`) VALUES (:json)", ["json"=>$json]);
	}

}
