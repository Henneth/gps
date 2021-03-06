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
		$data = DB::select("SELECT * FROM gps_live_{$event_id}.device_mapping T1
			LEFT JOIN gps_live_{$event_id}.athletes T2
			ON T2.bib_number = T1.bib_number
			ORDER BY T1.device_mapping_id DESC");
		return $data;
	}


	public static function getAthletesProfile($event_id, $auth, $visible_only, $live = false, $all = false) {

		if (!$auth) {
			$checkIsPublic = "AND is_public = 1 ";
		} else {
			$checkIsPublic = "";
		}

		if ($visible_only) {
			$checkIsVisible = "AND status = 'visible' ";
		} else {
			$checkIsVisible = "";
		}

		if ($live) {
			$profile = DB::select("SELECT * FROM gps_live_{$event_id}.participants WHERE 1=1 ".$checkIsPublic.$checkIsVisible."ORDER BY IF(bib_number RLIKE '^[a-z]', 2, 1), ABS(bib_number)");
			// echo '<pre>'.print_r($profile, 1).'</pre>';

		} else {
			$profile = DB::select("SELECT * FROM archive_participants
				WHERE event_id = :event_id ".$checkIsPublic.$checkIsVisible."ORDER BY IF(bib_number RLIKE '^[a-z]', 2, 1), ABS(bib_number)", [
					"event_id"=>$event_id
				]);
			// echo '<pre>'.print_r($profile, 1).'</pre>';
		}

		if ($all) {
			return $profile;
		}

		$count = 1;
		foreach ($profile as $key => &$athlete) {
			if ($athlete->status == 'visible'){
				if ($count > 20) {
					if ($visible_only) {
						unset($profile[$key]);
					} else {
						$athlete->status = 'hidden';
					}
				}
				$count++;
			}
		}
		return $profile;
	}

}


// ------
		// if (!$auth) {
		// 	$checkIsPublic = "AND athletes.is_public = 1 ";
		// } else {
		// 	$checkIsPublic = "";
		// }
		//
		// if ($visible_only) {
		// 	$checkIsVisible = "AND device_mapping.status = 'visible'";
		// } else {
		// 	$checkIsVisible = "";
		// }
		//
		// if ($live) {
		// 	$profile = DB::connection('gps_live')->select("SELECT * FROM device_mapping
		// 		INNER JOIN athletes ON (athletes.bib_number = device_mapping.bib_number AND athletes.event_id = device_mapping.event_id)
		// 		WHERE device_mapping.event_id = :event_id ".$checkIsPublic.$checkIsVisible.
		// 		"ORDER BY device_mapping_id", [
		// 			"event_id"=>$event_id
		//         ]);
		// } else {
		// 	$profile = DB::select("SELECT * FROM device_mapping
		// 		INNER JOIN athletes ON (athletes.bib_number = device_mapping.bib_number AND athletes.event_id = device_mapping.event_id)
		// 		WHERE device_mapping.event_id = :event_id ".$checkIsPublic.$checkIsVisible.
		// 		"ORDER BY device_mapping_id", [
		// 			"event_id"=>$event_id
		// 		]);
		// }
