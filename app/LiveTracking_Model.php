<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class LiveTracking_Model extends Model
{
// ---------------------------------
	// public static function getLatestLocations($event_id, $datetime_from, $datetime_to, $auth) {
	// 	if (!$auth) {
	// 		$checkIsPublic = "AND is_public = 1 ";
	// 	} else {
	// 		$checkIsPublic = "";
	// 	}
	// 	$data = DB::connection('gps_live')->select("SELECT * FROM (
	// 			SELECT gps_data.device_id AS device_id, datetime, id, latitude_final, longitude_final, athletes.athlete_id, athletes.bib_number, first_name, last_name, zh_full_name, is_public, country_code, country, colour_code, status FROM gps_data
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
	// 			ORDER BY datetime DESC, id DESC
	// 		) t
	// 	    GROUP BY t.device_id", [
	// 			"event_id1"=>$event_id,
	// 			"event_id2"=>$event_id,
	// 			"event_id3"=>$event_id,
	//             "datetime_from"=>$datetime_from,
	//             "datetime_to"=>$datetime_to
	//         ]);
	// 	return $data;
	// }
// ---------------------------------

	public static function getLocationsViaBibNumber($event_id, $datetime_from, $datetime_to, $bib_number, $color) {

		// $athlete = DB::connection('gps_live')->select("SELECT device_mapping.device_id, device_mapping.status, athletes.athlete_id, device_mapping.bib_number, athletes.first_name, athletes.first_name, athletes.last_name, athletes.zh_full_name, athletes.is_public, athletes.colour_code, countries.country, countries.code
		// 	FROM device_mapping
		// 	INNER JOIN athletes
		// 	ON (athletes.bib_number = device_mapping.bib_number AND athletes.event_id = device_mapping.event_id)
		// 	LEFT JOIN countries
		// 	ON (countries.code = athletes.country_code)
		// 	WHERE device_mapping.device_id =:device_id AND device_mapping.event_id =:event_id", ["device_id"=>$deviceID, "event_id"=>$event_id]);
		$athlete = DB::select("SELECT * FROM gps_live_{$event_id}.participants
			WHERE bib_number = :bib_number LIMIT 1",
			['bib_number'=>$bib_number ]);

		if (!empty($athlete)) {
			$athlete[0]->colour_code = $color;
		}
			// $start_time = $athlete[0]->start_time;
			// $end_time = $athlete[0]->end_time;
		// } else {
			// $start_time = NULL;
			// $end_time = NULL;
		// }

		// $distances = DB::connection('gps_live')->select("SELECT * FROM route_distances
		// 	INNER JOIN route_progress
		// 	ON route_distances.event_id = route_progress.event_id AND route_distances.route_index = route_progress.route_index
		// 	WHERE route_distances.event_id = :event_id
		// 	AND route_progress.device_id = :device_id
		// 	ORDER BY route_progress.reached_at ASC", [
		// 		'event_id' => $event_id,
		// 		'device_id' => $deviceID
		// 		] );
		$distances = DB::select("SELECT * FROM gps_live_{$event_id}.distance_data
			WHERE bib_number = :bib_number ORDER BY datetime ASC",
			['bib_number'=>$bib_number]);

		// can be improved
		// $reachedCheckpoint = DB::connection('gps_live')->select("SELECT checkpoint, reached_at, checkpoint_name, device_id, min_time FROM route_distances
		// 	INNER JOIN route_progress
		// 	ON route_progress.event_id = route_distances.event_id AND route_progress.route_index = route_distances.route_index
		// 	WHERE route_distances.event_id = :event_id
		// 	AND route_distances.is_checkpoint = 1
		// 	AND device_id = :device_id", [
		// 		'event_id' => $event_id,
		// 		'device_id'=>$deviceID
		// 		] );
		$reachedCheckpoint = DB::select("SELECT * FROM gps_live_{$event_id}.reached_checkpoint
			WHERE bib_number = :bib_number ORDER BY datetime ASC",
			['bib_number'=>$bib_number]);

		$finished_at = DB::select("SELECT datetime AS reached_at FROM gps_live_{$event_id}.reached_checkpoint
			WHERE bib_number = :bib_number
			AND checkpoint_id = (SELECT max(checkpoint_id) AS max_checkpoint_id FROM gps_live_{$event_id}.checkpoint) LIMIT 1",
			["bib_number"=>$bib_number]);
		$finished_at = !empty($finished_at) ? $finished_at[0]->reached_at : null;

		// $data = DB::select("SELECT * FROM gps_live_{$event_id}.valid_data
		// 	WHERE bib_number = :bib_number
		// 	AND (:start_time IS NULL OR (:start_time1 IS NOT NULL AND datetime >= :start_time2))
		// 	AND (:end_time IS NULL OR (:end_time1 IS NOT NULL AND datetime <= :end_time2))
		// 	AND (:finished_at1 IS NULL OR (:finished_at2 IS NOT NULL AND datetime <= :finished_at3))
		// 	ORDER BY datetime DESC", [
		// 		"bib_number"=>$bib_number,
		// 		"start_time"=>$start_time,
		// 		"end_time"=>$end_time,
		// 		"start_time1"=>$start_time,
		// 		"end_time1"=>$end_time,
		// 		"start_time2"=>$start_time,
		// 		"end_time2"=>$end_time,
		// 		"finished_at1"=>$finished_at,
		// 		"finished_at2"=>$finished_at,
		// 		"finished_at3"=>$finished_at
		// 	]);
		$data = DB::select("SELECT *, unix_timestamp(datetime) AS timestamp FROM gps_live_{$event_id}.valid_data
			WHERE bib_number = :bib_number
			-- AND (:start_time IS NULL OR (:start_time1 IS NOT NULL AND datetime >= :start_time2))
			-- AND (:end_time IS NULL OR (:end_time1 IS NOT NULL AND datetime <= :end_time2))
			AND (:finished_at1 IS NULL OR (:finished_at2 IS NOT NULL AND datetime <= :finished_at3))
			ORDER BY datetime DESC", [
				"bib_number"=>$bib_number,
				"finished_at1"=>$finished_at,
				"finished_at2"=>$finished_at,
				"finished_at3"=>$finished_at
			]);
		// echo '<pre>'.print_r($data, 1).'</pre>';

		// $data = DB::connection('gps_live')->select("SELECT gps_data.datetime, unix_timestamp(datetime) AS timestamp, gps_data.id, gps_data.latitude_final, gps_data.longitude_final FROM gps_data
		// 	INNER JOIN device_mapping
		// 	ON gps_data.device_id = device_mapping.device_id
		// 	LEFT JOIN (SELECT device_id, reached_at from route_progress where event_id = :event_id1 and device_id = :device_id1 and route_index = (SELECT max(route_index) as maxrouteindex from route_distances where event_id = :event_id2)) t2
		// 	ON (t2.device_id = device_mapping.device_id)
		// 	WHERE device_mapping.event_id = :event_id3
		// 	AND device_mapping.device_id = :device_id2
		// 	AND datetime >= :datetime_from
		// 	AND datetime <= :datetime_to
		// 	AND (start_time IS NULL OR (start_time IS NOT NULL AND datetime >= start_time))
		// 	AND (end_time IS NULL OR (end_time IS NOT NULL AND datetime <= end_time))
		// 	AND (reached_at IS NULL AND datetime >= DATE_SUB(NOW(), INTERVAL 10 MINUTE) OR (reached_at IS NOT NULL AND datetime <= reached_at AND datetime >= DATE_SUB(reached_at, INTERVAL 10 MINUTE)))
		// 	ORDER BY datetime DESC, id DESC", [
		// 	"event_id1"=>$event_id,
		// 	"event_id2"=>$event_id,
		// 	"event_id3"=>$event_id,
		// 	"device_id1"=>$deviceID,
		// 	"device_id2"=>$deviceID,
		// 	"datetime_from"=>$datetime_from,
		// 	"datetime_to"=>$datetime_to
		// 	]);

		$array = [];
		$array['athlete'] = !empty($athlete) ? $athlete[0] : null;
		$array['data'] = $data;
		$array['distances'] = $distances;
		$array['reachedCheckpoint'] = $reachedCheckpoint;
		return $array;
	}

	public static function getReachedCheckpointData($event_id, $bib_number){
		$reached_checkpoint = DB::select("SELECT checkpoint_id, datetime FROM gps_live_{$event_id}.reached_checkpoint WHERE bib_number = :bib_number ORDER BY datetime desc", ["bib_number"=>$bib_number]);

		$name = DB::table("gps_live_{$event_id}.athletes")
			->where('bib_number', $bib_number)
			->select('first_name', 'last_name')
			->first();

		$array['name'] = $name->first_name . " " . (!empty($name->last_name) ? $name->last_name : "");
		$array['bib_number'] = $bib_number;
		$array['data'] = $reached_checkpoint;
		return $array;
	}


}

// ---------------------------------
	// get data from route_distances & route_progress table, get the largest route_index
	// public static function getRouteDistance($event_id){
	// 	$data = DB::connection('gps_live')->select("SELECT * FROM (SELECT distance, device_mapping.device_id, athletes.bib_number, route_distances.route_index, athletes.first_name, athletes.last_name, athletes.zh_full_name, athletes.colour_code FROM route_distances
	// 		INNER JOIN route_progress ON route_distances.event_id = route_progress.event_id AND route_distances.route_index = route_progress.route_index
	// 		INNER JOIN device_mapping ON route_progress.event_id = device_mapping.event_id AND route_progress.device_id = device_mapping.device_id
	// 		INNER JOIN athletes ON athletes.event_id = device_mapping.event_id AND athletes.bib_number = device_mapping.bib_number
	// 		WHERE route_distances.event_id = :event_id
	// 		ORDER BY route_distances.route_index DESC) t
	// 		GROUP BY device_id", ['event_id' => $event_id] );
	// 	return $data;
	// }
	//
	// public static function getRouteDistanceByDevice($event_id, $device_id){
	// 	$data = DB::connection('gps_live')->select("SELECT distance, route_progress.device_id, route_distances.route_index FROM route_distances
	// 		INNER JOIN route_progress ON route_distances.event_id = route_progress.event_id AND route_distances.route_index = route_progress.route_index
	// 		WHERE route_distances.event_id = :event_id AND route_progress.device_id = :device_id
	// 		ORDER BY route_distances.route_index DESC LIMIT 1", ['event_id' => $event_id, 'device_id' => $device_id]);
	// 	return $data;
	// }

	// public static function getCheckpointData($event_id) {
	// 	$data = DB::connection('gps_live')->select("SELECT * FROM route_distances
	// 		INNER JOIN route_progress
	// 		ON route_progress.event_id = route_distances.event_id AND route_progress.route_index = route_distances.route_index
	// 		WHERE route_distances.event_id = :event_id AND route_distances.is_checkpoint = 1
	// 		ORDER BY device_id, route_distances.route_index", ['event_id' => $event_id] );
	// 	return $data;
	// }
// ---------------------------------



	// // get athletes who finished the event. reached at end point
	// public static function getFinishedAthletes($event_id){
	// 	$data = DB::connection('gps_live')->select("SELECT device_id, route_progress.route_index FROM route_distances
	// 		LEFT JOIN route_progress ON route_progress.event_id = route_distances.event_id
	// 		WHERE route_progress.event_id = $event_id AND is_checkpoint = 1 AND route_progress.route_index = (select max(route_index) from route_distances) GROUP BY device_id");
	// 	return $data;
	// }


	// get data within 10 minutes for tail
	// public static function getTail($event_id) {
	//
    //     $ongoing = DB::connection('gps_live')->select("SELECT datetime_to > NOW() AS ongoing FROM events WHERE event_id = :event_id LIMIT 1", [
	// 		'event_id' => $event_id
	// 	]);
	// 	if ($ongoing[0]->ongoing) {
	// 		$upperTimeLimit = 'NOW()';
	// 	} else {
	// 		$upperTimeLimit = 'events.datetime_to';
	// 	}
	//
	// 	$data = DB::connection('gps_live')->select("SELECT events.event_id, gps_data.device_id, latitude_final, longitude_final, colour_code
	// 		FROM gps_data
	// 		INNER JOIN device_mapping ON device_mapping.device_id = gps_data.device_id
	// 		INNER JOIN events ON events.event_id = device_mapping.event_id
	// 		INNER JOIN athletes ON (athletes.event_id = device_mapping.event_id AND athletes.bib_number = device_mapping.bib_number)
	// 		LEFT JOIN (SELECT device_id, reached_at from route_progress where event_id = :event_id1 and route_index = (SELECT max(route_index) as maxrouteindex from route_distances where event_id = :event_id2 )) t2
	// 		ON (t2.device_id = device_mapping.device_id)
	// 		WHERE events.event_id = :event_id3
	// 		AND DATE_SUB(".$upperTimeLimit.", INTERVAL 10 MINUTE) < gps_data.datetime AND gps_data.datetime < ".$upperTimeLimit."
	// 		AND (reached_at IS NULL OR (reached_at IS NOT NULL AND gps_data.datetime <= reached_at))", [
	// 			'event_id1' => $event_id,
	// 			'event_id2' => $event_id,
	// 			'event_id3' => $event_id,
	// 		]);
	// 	return $data;
	// }
