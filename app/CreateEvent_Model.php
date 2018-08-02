<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class CreateEvent_Model extends Model
{
	// create new gps_live DB for new create event
	public static function createLiveDB($event_id) {
		// DB::connection()->statement('CREATE DATABASE :schema', ['schema' => "GDP"]);
		// DB::unprepared(file_get_contents(app_path()."/database/seeds/members.sql"));
		DB::unprepared('CREATE DATABASE IF NOT EXISTS `gps_live_'.$event_id.'` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
		USE `gps_live_'.$event_id.'`;'.file_get_contents(storage_path('/')."live_db.sql"));
	}
}
