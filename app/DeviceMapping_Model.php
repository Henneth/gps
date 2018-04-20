<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class DeviceMapping_Model extends Model
{
	// used on device mapping page
	public static function getDeviceMappings($event_id) {
		$data = DB::select("SELECT * FROM device_mapping 
			LEFT JOIN athletes ON (athletes.bib_number = device_mapping.bib_number AND athletes.event_id = device_mapping.event_id)
			WHERE device_mapping.event_id = :event_id
			ORDER BY device_mapping_id DESC", [
				"event_id"=>$event_id
	        ]);
		return $data;
	}

	// used on athletes page
	public static function getAthletesProfile($event_id) {
		$profile = DB::select("SELECT * FROM device_mapping 
			INNER JOIN athletes ON (athletes.bib_number = device_mapping.bib_number AND athletes.event_id = device_mapping.event_id)
			WHERE device_mapping.event_id = :event_id
			ORDER BY device_mapping_id DESC", [
				"event_id"=>$event_id
	        ]);
		return $profile;
	}


}
