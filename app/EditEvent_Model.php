<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class EditEvent_Model extends Model
{
	// Tables will be copied to live DB
	public static function processAthletes($event_id) {
		$live_db = "gps_live_{$event_id}";
		DB::insert("INSERT INTO {$live_db}.participants (bib_number, first_name, last_name, zh_full_name, is_public, country_code, country, country_zh_hk, start_time, end_time, status) SELECT bib_number, first_name, last_name, zh_full_name, is_public, country_code, country, country_zh_hk, null, null, status FROM {$live_db}.athletes AS a LEFT JOIN {$live_db}.countries AS c ON a.country_code = c.code");
	}
	// Tables will be copied to archive DB
	public static function copyToArchiveDB($event_id) {
		DB::transaction(function () use ($event_id) {
			$tables = ["athletes", "checkpoint", "device_mapping", "distance_data", "invalid_data", "map_point", "participants", "reached_checkpoint", "valid_data"];
			foreach ($tables as $table) {
				$target = "gps.archive_{$table}";
				$origin = "gps_live_{$event_id}.{$table}";
				DB::insert("INSERT INTO {$target} SELECT {$event_id}, {$origin}.* FROM {$origin}");
			}

			DB::insert("INSERT INTO raw_data (event_id, device_id, latitude, longitude, battery_level, datetime, created_at) SELECT {$event_id}, device_id, latitude, longitude, battery_level, datetime, created_at FROM gps_live_{$event_id}.raw_data");
		});
	}

	public static function revertToOriginal($event_id) {
		DB::transaction(function () use ($event_id) {
			$tables = ["distance_data", "invalid_data", "next_checkpoint", "participants", "reached_checkpoint", "valid_data"];
			foreach ($tables as $table) {
				DB::table("gps_live_{$event_id}.{$table}")->truncate();
			}

			DB::update("UPDATE gps_live_{$event_id}.raw_data SET processed = 0");
		});
	}
}
