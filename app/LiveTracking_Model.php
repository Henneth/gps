<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class LiveTracking_Model extends Model
{
	public static function getLatestLocations($event_id, $datetime_from, $datetime_to) {
		$data = DB::select("SELECT * FROM (
				SELECT gps_data.device_id AS device_id, datetime, id, latitude_final, longitude_final, athletes.athlete_id, athletes.bib_number, first_name, last_name, country_code, colour_code FROM gps_data
				INNER JOIN device_mapping
				ON gps_data.device_id = device_mapping.device_id
				INNER JOIN athletes
				ON device_mapping.athlete_id = athletes.athlete_id
				WHERE device_mapping.event_id = :event_id
				AND datetime >= :datetime_from AND datetime <= :datetime_to
				AND (start_time IS NULL OR (start_time IS NOT NULL AND datetime >= start_time))
				AND (end_time IS NULL OR (end_time IS NOT NULL AND datetime <= end_time))
				AND device_mapping.status = 'visible'
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
