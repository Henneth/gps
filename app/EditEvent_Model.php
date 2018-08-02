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
		DB::transaction(function () use ($event_id) {
			$tables = ["athletes", "checkpoint", "device_mapping", "invalid_data", "map_point", "participants", "reached_checkpoint", "valid_data"];
			foreach ($tables as $table) {
				$target = "gps.archive_{$table}";
				$origin = "gps_live_{$event_id}.{$table}";
				DB::insert("INSERT INTO {$target} SELECT :event_id AS event_id, {$origin}.* FROM {$origin}", ["event_id"=>$event_id]);
			}
		});
	}
}
