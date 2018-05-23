<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class ReplayTracking_Model extends Model
{
	public static function getLocations($event_id, $datetime_from, $datetime_to) {
		$data = DB::select("SELECT gps_data.device_id AS device_id, datetime, unix_timestamp(datetime) AS timestamp, id, latitude_final, longitude_final, athletes.athlete_id, athletes.bib_number, first_name, last_name, zh_full_name, is_public, country_code, country, colour_code, status
				FROM gps_data
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
				ORDER BY datetime DESC, id DESC", [
				"event_id"=>$event_id,
	            "datetime_from"=>$datetime_from,
	            "datetime_to"=>$datetime_to
	        ]);

	        // echo "<pre>".print_r($data, 1)."</pre>";
		return $data;
	}

	public static function getLocations2($event_id, $datetime_from, $datetime_to) {
		$data = DB::select("SELECT gps_data.device_id AS device_id, datetime, unix_timestamp(datetime) AS timestamp, id, latitude_final, longitude_final, athletes.athlete_id, athletes.bib_number, first_name, last_name, zh_full_name, is_public, country_code, country, colour_code, status
				FROM gps_data
				INNER JOIN device_mapping
				ON gps_data.device_id = device_mapping.device_id
				INNER JOIN athletes
				ON (athletes.bib_number = device_mapping.bib_number AND athletes.event_id = device_mapping.event_id)
				LEFT JOIN countries
				ON (countries.code = athletes.country_code)
				WHERE device_mapping.event_id = :event_id
				AND athletes.is_public = 1
				AND datetime >= :datetime_from AND datetime <= :datetime_to
				AND (start_time IS NULL OR (start_time IS NOT NULL AND datetime >= start_time))
				AND (end_time IS NULL OR (end_time IS NOT NULL AND datetime <= end_time))
				ORDER BY datetime DESC, id DESC", [
				"event_id"=>$event_id,
				"datetime_from"=>$datetime_from,
				"datetime_to"=>$datetime_to
			]);

			// echo "<pre>".print_r($data, 1)."</pre>";
		return $data;
	}

	// get data from route_distances & route_progress table, get the largest route_index
	public static function getRouteDistance($event_id){
		$data = DB::select("SELECT distance, device_mapping.device_id, athletes.bib_number, route_distances.route_index, route_progress.reached_at, athletes.first_name, athletes.last_name, athletes.zh_full_name FROM route_distances
		INNER JOIN route_progress ON route_distances.event_id = route_progress.event_id AND route_distances.route_index = route_progress.route_index
		INNER JOIN device_mapping ON route_progress.event_id = device_mapping.event_id AND route_progress.device_id = device_mapping.device_id
		INNER JOIN athletes ON athletes.event_id = device_mapping.event_id AND athletes.bib_number = device_mapping.bib_number
		WHERE route_distances.event_id = :event_id
		ORDER BY route_progress.reached_at ASC", ['event_id' => $event_id] );
		return $data;
	}

}
