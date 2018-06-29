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
		DB::table('gps_live.athletes')->truncate();
		DB::table('gps_live.device_mapping')->truncate();
		DB::table('gps_live.events')->truncate();
		DB::table('gps_live.last_id')->truncate();
		DB::table('gps_live.routes')->truncate();
		DB::table('gps_live.route_distances')->truncate();
		DB::table('gps_live.route_progress')->truncate();
		DB::insert("INSERT INTO gps_live.athletes SELECT * FROM gps.athletes WHERE event_id = :event_id", ['event_id'=>$event_id]);
		DB::insert("INSERT INTO gps_live.device_mapping SELECT * FROM gps.device_mapping WHERE event_id = :event_id", ['event_id'=>$event_id]);
		DB::insert("INSERT INTO gps_live.events SELECT * FROM gps.events WHERE event_id = :event_id", ['event_id'=>$event_id]);
		DB::insert("INSERT INTO gps_live.last_id SELECT * FROM gps.last_id WHERE event_id = :event_id", ['event_id'=>$event_id]);
		DB::insert("INSERT INTO gps_live.routes SELECT * FROM gps.routes WHERE event_id = :event_id", ['event_id'=>$event_id]);
		DB::insert("INSERT INTO gps_live.route_distances SELECT * FROM gps.route_distances WHERE event_id = :event_id", ['event_id'=>$event_id]);
		// DB::insert("INSERT INTO gps_live.route_progress SELECT * FROM gps.route_progress WHERE event_id = :event_id", ['event_id'=>$event_id]);
	}
	public static function copyToArchiveDB($event_id) {
		shell_exec("php ".public_path()."/calculation.php");

		DB::insert("INSERT INTO gps.gps_data(device_id, latitude, latitude_logo, latitude_final, longitude, longitude_logo, longitude_final, elevation, battery_level, is_valid, `datetime`, created_at) SELECT device_id, latitude, latitude_logo, latitude_final, longitude, longitude_logo, longitude_final, elevation, battery_level, is_valid, `datetime`, created_at FROM gps_live.gps_data");
		DB::delete("DELETE FROM gps.route_progress WHERE event_id = :event_id", ['event_id'=>$event_id]);
		DB::insert("INSERT INTO gps.route_progress SELECT * FROM gps_live.route_progress WHERE event_id = :event_id", ['event_id'=>$event_id]);
		DB::table('gps_live.athletes')->truncate();
		DB::table('gps_live.device_mapping')->truncate();
		DB::table('gps_live.events')->truncate();
		DB::table('gps_live.last_id')->truncate();
		DB::table('gps_live.routes')->truncate();
		DB::table('gps_live.route_distances')->truncate();
		DB::table('gps_live.route_progress')->truncate();
		DB::table('gps_live.gps_data')->truncate();
	}
}
