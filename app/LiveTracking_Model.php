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
				SELECT * FROM gps_data
				WHERE datetime >= :datetime_from AND datetime <= :datetime_to
				ORDER BY datetime DESC
			) t
			INNER JOIN device_mapping
			ON t.device_id = device_mapping.device_id
			INNER JOIN athletes
			ON device_mapping.athlete_id = athletes.athlete_id
			WHERE device_mapping.event_id = :event_id
		    GROUP BY t.device_id", [
				"event_id"=>$event_id,
	            "datetime_from"=>$datetime_from,
	            "datetime_to"=>$datetime_to
	        ]);
		return $data;
	}

}
