<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class EditEvent_Model extends Model
{
	// Tables will be copied to live DB
	public static function copyToLiveDB($event_id) {
		// DB::transaction(function () use ($event_id) {
		// 	DB::table("gps_live_".$event_id.".events")->truncate();
		// 	DB::insert("INSERT INTO gps_live_".$event_id.".events SELECT * FROM gps.events WHERE event_id = :event_id", ["event_id"=>$event_id]);
		// });

	}
	// Tables will be copied to archive DB
	public static function copyToArchiveDB($event_id) {
		// DB::transaction(function () use ($event_id) {

			$tables = ["athletes", "checkpoint", "device_mapping", "invalid_data", "map_point", "participants", "reached_checkpoint", "valid_data"];
			echo '<pre>'.print_r($tables, 1).'</pre>';

			foreach ($tables as $table) {

				$target = "gps.archive_{$table}";
				$origin = "gps_live_{$event_id}.{$table}";
				// $data = DB::insert("INSERT INTO {$target} SELECT ':event_id' AS event_id, {$origin}.* FROM {$origin}", ["event_id"=>$event_id]);
				// $data = DB::insert("INSERT INTO {$target} SELECT gps.events.event_id AS event_id, {$origin}.* FROM {$origin} INNER JOIN gps.events ON gps.events.event_id = gps_live_{$event_id}.events.event_id WHERE gps.events.event_id = :event_id", ["event_id"=>$event_id]);

				$data = DB::insert("INSERT INTO {$target} SELECT gps.events.event_id AS event_id, {$origin}.* FROM {$origin} LEFT JOIN gps.events ON gps.events.event_id = gps_live_{$event_id}.events.event_id WHERE gps.events.event_id = :event_id", ["event_id"=>$event_id]);

				// DB::insert("INSERT INTO {$target} SELECT :event_id AS event_id, {$origin}.* FROM {$origin}", ["event_id"=>$event_id]);
				echo '<pre>'.print_r($target, 1).'</pre>';
				echo '<pre>'.print_r($origin, 1).'</pre>';

			}
			echo '<pre>'.print_r($data, 1).'</pre>';
		// });
	}
}
