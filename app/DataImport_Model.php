<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class DataImport_Model extends Model
{
	public static function storeGPSData($longitude, $longitude_logo, $longitude_final, $latitude, $latitude_logo, $latitude_final, $date, $time) {
		DB::insert("INSERT INTO gps
            (`longitude`,`longitude_logo`, `longitude_final`,`latitude`,`latitude_logo`, `latitude_final`, `date`, `time`)
            VALUES (:longitude, :longitude_logo, :longitude_final, :latitude, :latitude_logo, latitude_final, :date, :time)", [
                "longitude"=>$longitude,
                "longitude_logo"=>$longitude_logo,
                "longitude_final"=>$longitude_final,
                "latitude"=>$latitude,
                "latitude_logo"=>$latitude_logo,
                "latitude_final"=>$latitude_final,
                "date"=>$date,
                "time"=>$time
            ]);
	}
	public static function insertRawData($json) {
		DB::insert("INSERT INTO gps_raw (`raw`) VALUES (:json)", ["json"=>$json]);
	}

}
