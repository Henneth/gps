<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class LiveTracking_Model extends Model
{
	// admin 
	public static function getLatestLocations($event_id, $datetime_from, $datetime_to) {
		$data = DB::select("SELECT * FROM (
				SELECT gps_data.device_id AS device_id, datetime, id, latitude_final, longitude_final, athletes.athlete_id, athletes.bib_number, first_name, last_name, zh_full_name, is_public, country_code, country, colour_code, status FROM gps_data
				INNER JOIN device_mapping
				ON gps_data.device_id = device_mapping.device_id
				INNER JOIN athletes
				ON (athletes.bib_number = device_mapping.bib_number AND athletes.event_id = device_mapping.event_id)
				LEFT JOIN countries
				ON (countries.code = athletes.country_code)
				WHERE device_mapping.event_id = :event_id
				AND datetime >= :datetime_from AND datetime <= :datetime_to
				AND (start_time IS NULL OR (start_time IS NOT NULL AND datetime >= start_time))
				AND (end_time IS NULL OR (end_time IS NOT NULL AND datetime <= end_time))
				ORDER BY datetime DESC, id DESC
			) t
		    GROUP BY t.device_id", [
				"event_id"=>$event_id,
	            "datetime_from"=>$datetime_from,
	            "datetime_to"=>$datetime_to
	        ]);
		return $data;
	}
	// user without login
	public static function getLatestLocations2($event_id, $datetime_from, $datetime_to) {
		$data = DB::select("SELECT * FROM (
				SELECT gps_data.device_id AS device_id, datetime, id, latitude_final, longitude_final, athletes.athlete_id, athletes.bib_number, first_name, last_name, zh_full_name, is_public, country_code, country, colour_code, status FROM gps_data
				INNER JOIN device_mapping
				ON gps_data.device_id = device_mapping.device_id
				INNER JOIN athletes
				ON (athletes.bib_number = device_mapping.bib_number AND athletes.event_id = device_mapping.event_id)
				LEFT JOIN countries
				ON (countries.code = athletes.country_code)
				WHERE device_mapping.event_id = :event_id
				AND is_public = 1
				AND datetime >= :datetime_from AND datetime <= :datetime_to
				AND (start_time IS NULL OR (start_time IS NOT NULL AND datetime >= start_time))
				AND (end_time IS NULL OR (end_time IS NOT NULL AND datetime <= end_time))
				ORDER BY datetime DESC, id DESC
			) t
			GROUP BY t.device_id", [
				"event_id"=>$event_id,
				"datetime_from"=>$datetime_from,
				"datetime_to"=>$datetime_to
			]);
		return $data;
	}

}
