<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class LiveTracking_Model extends Model
{
	// admin
	public static function getLatestLocations($event_id, $datetime_from, $datetime_to, $auth) {
		if (!$auth) {
			$checkIsPublic = "AND is_public = 1 ";
		} else {
			$checkIsPublic = "";
		}
		$data = DB::select("SELECT * FROM (
				SELECT gps_data.device_id AS device_id, datetime, id, latitude_final, longitude_final, athletes.athlete_id, athletes.bib_number, first_name, last_name, zh_full_name, is_public, country_code, country, colour_code, status FROM gps_data
				INNER JOIN device_mapping
				ON gps_data.device_id = device_mapping.device_id
				INNER JOIN athletes
				ON (athletes.bib_number = device_mapping.bib_number AND athletes.event_id = device_mapping.event_id)
				LEFT JOIN countries
				ON (countries.code = athletes.country_code)
				WHERE device_mapping.event_id = :event_id ".$checkIsPublic.
				"AND datetime >= :datetime_from AND datetime <= :datetime_to
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
	// // user without login
	// public static function getLatestLocations2($event_id, $datetime_from, $datetime_to) {
	// 	$data = DB::select("SELECT * FROM (
	// 			SELECT gps_data.device_id AS device_id, datetime, id, latitude_final, longitude_final, athletes.athlete_id, athletes.bib_number, first_name, last_name, zh_full_name, is_public, country_code, country, colour_code, status FROM gps_data
	// 			INNER JOIN device_mapping
	// 			ON gps_data.device_id = device_mapping.device_id
	// 			INNER JOIN athletes
	// 			ON (athletes.bib_number = device_mapping.bib_number AND athletes.event_id = device_mapping.event_id)
	// 			LEFT JOIN countries
	// 			ON (countries.code = athletes.country_code)
	// 			WHERE device_mapping.event_id = :event_id
	// 			AND is_public = 1
	// 			AND datetime >= :datetime_from AND datetime <= :datetime_to
	// 			AND (start_time IS NULL OR (start_time IS NOT NULL AND datetime >= start_time))
	// 			AND (end_time IS NULL OR (end_time IS NOT NULL AND datetime <= end_time))
	// 			ORDER BY datetime DESC, id DESC
	// 		) t
	// 		GROUP BY t.device_id", [
	// 			"event_id"=>$event_id,
	// 			"datetime_from"=>$datetime_from,
	// 			"datetime_to"=>$datetime_to
	// 		]);
	// 	return $data;
	// }

	// get data from route_distances & route_progress table, get the largest route_index
	public static function getRouteDistance($event_id){
		$data = DB::select("SELECT * FROM (SELECT distance, device_mapping.device_id, athletes.bib_number, route_distances.route_index, athletes.first_name, athletes.last_name, athletes.zh_full_name FROM route_distances
			INNER JOIN route_progress ON route_distances.event_id = route_progress.event_id AND route_distances.route_index = route_progress.route_index
			INNER JOIN device_mapping ON route_progress.event_id = device_mapping.event_id AND route_progress.device_id = device_mapping.device_id
			INNER JOIN athletes ON athletes.event_id = device_mapping.event_id AND athletes.bib_number = device_mapping.bib_number
			WHERE route_distances.event_id = :event_id
			ORDER BY route_distances.route_index DESC) t
			GROUP BY device_id", ['event_id' => $event_id] );
		return $data;
	}

	public static function getRouteDistanceByDevice($event_id, $device_id){
		$data = DB::select("SELECT distance, route_progress.device_id, route_distances.route_index FROM route_distances
			INNER JOIN route_progress ON route_distances.event_id = route_progress.event_id AND route_distances.route_index = route_progress.route_index
			WHERE route_distances.event_id = :event_id AND route_progress.device_id = :device_id
			ORDER BY route_distances.route_index DESC LIMIT 1", ['event_id' => $event_id, 'device_id' => $device_id]);
		return $data;
	}

}
