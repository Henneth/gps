<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class ReplayTracking_Model extends Model
{
	// public static function getLocations($event_id, $datetime_from, $datetime_to, $auth) {
	// 	if (!$auth) {
	// 		$checkIsPublic = "AND is_public = 1 ";
	// 	} else {
	// 		$checkIsPublic = "";
	// 	}
	//
	// 	// for ($i=0; $i < device_id; $i++) {
	// 	// 	$data = DB::select("select gps_data where device_id = :device_id")
	// 	// 	$alldata[] = $data
	// 	// }
	// 	$data = DB::select("SELECT gps_data.device_id AS device_id, datetime, unix_timestamp(datetime) AS timestamp, id, latitude_final, longitude_final, athletes.athlete_id, athletes.bib_number, first_name, last_name, zh_full_name, is_public, country_code, country, colour_code, status
	// 			FROM gps_data
	// 			INNER JOIN device_mapping
	// 			ON gps_data.device_id = device_mapping.device_id
	// 			INNER JOIN athletes
	// 			ON (athletes.bib_number = device_mapping.bib_number AND athletes.event_id = device_mapping.event_id)
	// 			LEFT JOIN countries
	// 			ON (countries.code = athletes.country_code)
	// 			LEFT JOIN (SELECT device_id, reached_at from route_progress where event_id = :event_id1 and route_index = (SELECT max(route_index) as maxrouteindex from route_distances where event_id = :event_id2 )) t2
	// 			ON (t2.device_id = device_mapping.device_id)
	// 			WHERE device_mapping.event_id = :event_id3 ".$checkIsPublic.
	// 			"AND datetime >= :datetime_from AND datetime <= :datetime_to
	// 			AND (start_time IS NULL OR (start_time IS NOT NULL AND datetime >= start_time))
	// 			AND (end_time IS NULL OR (end_time IS NOT NULL AND datetime <= end_time))
	// 			AND (reached_at IS NULL OR (reached_at IS NOT NULL AND datetime <= reached_at))
	// 			ORDER BY datetime DESC, id DESC LIMIT 10", [
	// 			"event_id1"=>$event_id,
	// 			"event_id2"=>$event_id,
	// 			"event_id3"=>$event_id,
	//             "datetime_from"=>$datetime_from,
	//             "datetime_to"=>$datetime_to
	//         ]);
	//
	//         // echo "<pre>".print_r($data, 1)."</pre>";
	// 	return $data;
	// }

	// public static function getLocations($event_id, $datetime_from, $datetime_to, $auth) {
	// 	if (!$auth) {
	// 		$checkIsPublic = "AND is_public = 1 ";
	// 	} else {
	// 		$checkIsPublic = "";
	// 	}
	//
	// 	// for ($i=0; $i < device_id; $i++) {
	// 	// 	$data = DB::seelct("select gps_data where device_id = :device_id")
	// 	// 	$alldata[] = $data
	// 	// }
	// 	$data = DB::select("SELECT gps_data.device_id AS device_id, datetime, unix_timestamp(datetime) AS timestamp, id, latitude_final, longitude_final, athletes.athlete_id, athletes.bib_number, first_name, last_name, zh_full_name, is_public, country_code, country, colour_code, status
	// 			FROM gps_data
	// 			INNER JOIN device_mapping
	// 			ON gps_data.device_id = device_mapping.device_id
	// 			INNER JOIN athletes
	// 			ON (athletes.bib_number = device_mapping.bib_number AND athletes.event_id = device_mapping.event_id)
	// 			LEFT JOIN countries
	// 			ON (countries.code = athletes.country_code)
	// 			LEFT JOIN (SELECT device_id, reached_at from route_progress where event_id = :event_id1 and route_index = (SELECT max(route_index) as maxrouteindex from route_distances where event_id = :event_id2 )) t2
	// 			ON (t2.device_id = device_mapping.device_id)
	// 			WHERE device_mapping.event_id = :event_id3 ".$checkIsPublic.
	// 			"AND datetime >= :datetime_from AND datetime <= :datetime_to
	// 			AND (start_time IS NULL OR (start_time IS NOT NULL AND datetime >= start_time))
	// 			AND (end_time IS NULL OR (end_time IS NOT NULL AND datetime <= end_time))
	// 			AND (reached_at IS NULL OR (reached_at IS NOT NULL AND datetime <= reached_at))
	// 			ORDER BY datetime DESC, id DESC LIMIT 10", [
	// 			"event_id1"=>$event_id,
	// 			"event_id2"=>$event_id,
	// 			"event_id3"=>$event_id,
	//             "datetime_from"=>$datetime_from,
	//             "datetime_to"=>$datetime_to
	//         ]);
	//
	//         // echo "<pre>".print_r($data, 1)."</pre>";
	// 	return $data;
	// }

	public static function getLocationsViaBibNumber($event_id, $datetime_from, $datetime_to, $bib_number, $color) {
		// $athlete = DB::select("SELECT device_mapping.device_id, device_mapping.status, athletes.athlete_id, device_mapping.bib_number, athletes.first_name, athletes.first_name, athletes.last_name, athletes.zh_full_name, athletes.is_public, athletes.colour_code, countries.country, countries.code
		// 	FROM device_mapping
		// 	INNER JOIN athletes
		// 	ON (athletes.bib_number = device_mapping.bib_number AND athletes.event_id = device_mapping.event_id)
		// 	LEFT JOIN countries
		// 	ON (countries.code = athletes.country_code)
		// 	WHERE device_mapping.device_id =:device_id AND device_mapping.event_id =:event_id LIMIT 1", [
		// 		"device_id"=>$deviceID,
		// 		"event_id"=>$event_id
		// 	]);

		$athlete = DB::select('SELECT * FROM archive_participants
			WHERE event_id = :event_id AND bib_number = :bib_number LIMIT 1',
			['event_id'=>$event_id, 'bib_number'=>$bib_number]);

		if (!empty($athlete)) {
			$athlete[0]->colour_code = $color;
		}
			// $start_time = $athlete[0]->start_time;
			// $end_time = $athlete[0]->end_time;
		// } else {
			// $start_time = NULL;
			// $end_time = NULL;
		// }

		// $distances = DB::select("SELECT * FROM route_distances
		// 	INNER JOIN route_progress
		// 	ON route_distances.event_id = route_progress.event_id AND route_distances.route_index = route_progress.route_index
		// 	WHERE route_distances.event_id = :event_id
		// 	AND route_progress.device_id = :device_id
		// 	ORDER BY route_progress.reached_at ASC, route_distances.route_distance_id", [
		// 		'event_id' => $event_id,
		// 		'device_id' => $deviceID
		// 	]);
		$distances = DB::select('SELECT * FROM archive_distance_data
			WHERE event_id = :event_id AND bib_number = :bib_number ORDER BY datetime ASC',
			['event_id'=>$event_id, 'bib_number'=>$bib_number]);

		// echo '<pre>'.print_r($distances, 1).'</pre>';

		// $checkpointData = [];
		// foreach ($distances as $distance) {
		// 	if ($distance->is_checkpoint == 1) {
		// 		$checkpointData[] = [
		// 			'checkpoint' => $distance->checkpoint,
		// 			'reached_at' => $distance->reached_at
		// 		];
		// 	}
		// }

		$reachedCheckpoint = DB::select('SELECT * FROM archive_reached_checkpoint
			WHERE event_id = :event_id AND bib_number = :bib_number ORDER BY datetime ASC',
			['event_id'=>$event_id, 'bib_number'=>$bib_number]);

		$finished_at = DB::select("SELECT datetime AS reached_at FROM archive_reached_checkpoint
			WHERE event_id = :event_id1 AND bib_number = :bib_number
			AND checkpoint_id = (SELECT max(checkpoint_id) AS max_checkpoint_id FROM archive_checkpoint WHERE event_id = :event_id2) LIMIT 1", [
				"event_id1"=>$event_id,
				"event_id2"=>$event_id,
				"bib_number"=>$bib_number
			]);
		$finished_at = !empty($finished_at) ? $finished_at[0]->reached_at : null;

		$data = DB::select("SELECT *, unix_timestamp(datetime) AS timestamp FROM archive_valid_data
			WHERE event_id = :event_id
			AND bib_number = :bib_number
			AND (:finished_at1 IS NULL OR (:finished_at2 IS NOT NULL AND datetime <= :finished_at3))
			ORDER BY datetime DESC", [
				"event_id"=>$event_id,
				"bib_number"=>$bib_number,
				"finished_at1"=>$finished_at,
				"finished_at2"=>$finished_at,
				"finished_at3"=>$finished_at
			]);

		$array = [];
		$array['athlete'] = !empty($athlete) ? $athlete[0] : null;
		$array['data'] = $data;
		$array['distances'] = $distances;
		$array['reachedCheckpoint'] = $reachedCheckpoint;
		return $array;
	}

	public static function getReachedCheckpointData($event_id, $bib_number){
		$reached_checkpoint = DB::select("SELECT checkpoint_id, datetime FROM archive_reached_checkpoint WHERE event_id = :event_id AND bib_number = :bib_number ORDER BY datetime desc", ["event_id"=>$event_id, "bib_number"=>$bib_number]);

		$name = DB::table("archive_athletes")
			->where('event_id', $event_id)
			->where('bib_number', $bib_number)
			->select('first_name', 'last_name')
			->first();

		$array['name'] = $name->first_name . " " . (!empty($name->last_name) ? $name->last_name : "");
		$array['bib_number'] = $bib_number;
		$array['data'] = $reached_checkpoint;
		return $array;
	}


}
// echo "<pre>".print_r($array,1)."</pre>";
