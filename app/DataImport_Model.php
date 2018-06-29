<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class DataImport_Model extends Model
{
	public static function storeGPSData($device_id, $longitude, $longitude_logo, $longitude_final, $latitude, $latitude_logo, $latitude_final, $elevation, $datetime) {
		$data = DB::table('events')
            ->select('current')
            ->where('current', 1)
            ->first();
        if ($data && $data->current == 1){
            DB::connection('gps_live')->insert("INSERT INTO gps_data
                (`device_id`, `longitude`,`longitude_logo`, `longitude_final`,`latitude`,`latitude_logo`, `latitude_final`, `elevation`, `datetime`)
                VALUES (:device_id, :longitude, :longitude_logo, :longitude_final, :latitude, :latitude_logo, :latitude_final, :elevation, :datetime)", [
                    "device_id"=>$device_id,
                    "longitude"=>$longitude,
                    "longitude_logo"=>$longitude_logo,
                    "longitude_final"=>$longitude_final,
                    "latitude"=>$latitude,
                    "latitude_logo"=>$latitude_logo,
                    "latitude_final"=>$latitude_final,
                    "elevation"=>$elevation,
                    "datetime"=>$datetime
                ]);
        }else {
            DB::insert("INSERT INTO gps_data
                (`device_id`, `longitude`,`longitude_logo`, `longitude_final`,`latitude`,`latitude_logo`, `latitude_final`, `elevation`, `datetime`)
                VALUES (:device_id, :longitude, :longitude_logo, :longitude_final, :latitude, :latitude_logo, :latitude_final, :elevation, :datetime)", [
                    "device_id"=>$device_id,
                    "longitude"=>$longitude,
                    "longitude_logo"=>$longitude_logo,
                    "longitude_final"=>$longitude_final,
                    "latitude"=>$latitude,
                    "latitude_logo"=>$latitude_logo,
                    "latitude_final"=>$latitude_final,
    				"elevation"=>$elevation,
                    "datetime"=>$datetime
                ]);
        }

	}

	public static function insertRawData($json) {
		DB::insert("INSERT INTO gps_raw (`raw`) VALUES (:json)", ["json"=>$json]);
	}
}
