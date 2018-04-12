<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class AthletesController extends Controller {

    public function index($event_id) {
        $athletes = DB::table('athletes')
            ->leftJoin('countries', 'athletes.country_code', '=', 'countries.code')
            ->orderby('athlete_id', 'desc')
            ->get();

        $countries = DB::table('countries')
        	->orderby('code')
            ->get();
        return view('athletes')->with(array('athletes' => $athletes, 'event_id' => $event_id, 'countries' => $countries));
    }
    public function addAthlete($event_id) {
        if (empty($_POST['bib_number']) || empty($_POST['first_name'])) {
            return redirect('event/'.$event_id.'/athletes')->with('error', 'Bib number and first name must not be empty.');
        }

        DB::table('athletes')->insert([
            'bib_number' => $_POST['bib_number'],
            'first_name' => $_POST['first_name'],
            'last_name' => !empty($_POST['last_name']) ? $_POST['last_name'] : NULL,
            'country_code' => !empty($_POST['country_code']) ? $_POST['country_code'] : NULL,
            'colour_code' => !empty($_POST['colour_code']) ? $_POST['colour_code'] : NULL,
        ]);
        return redirect('event/'.$event_id.'/athletes')->with('success', 'Athlete is added.');
    }
    public function editAthlete($event_id) {
        // print_r($_POST);
        if (empty($_POST['athlete_id']) || empty($_POST['bib_number']) || empty($_POST['first_name'])) {
            return redirect('event/'.$event_id.'/athletes')->with('error', 'Athlete ID, bib number and first name must not be empty.');
        }

        DB::table('athletes')
            ->where('athlete_id', $_POST['athlete_id'])
            ->update([
            'bib_number' => $_POST['bib_number'],
            'first_name' => $_POST['first_name'],
            'last_name' => !empty($_POST['last_name']) ? $_POST['last_name'] : NULL,
            'country_code' => !empty($_POST['country_code']) ? $_POST['country_code'] : NULL,
            'colour_code' => !empty($_POST['colour_code']) ? $_POST['colour_code'] : NULL,
        ]);
        return redirect('event/'.$event_id.'/athletes')->with('success', 'Athlete is edited.');
    }
    public function importFromExcel($event_id) {

        $target_dir = storage_path('app/athletes');
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

		// Check if file already exists
		if (file_exists($target_file)) {
            return redirect('event/'.$event_id.'/athletes')->with('error', 'Excel file with the same name has been uploaded.');
		}
		// Check file size
		// if ($_FILES["fileToUpload"]["size"] > 500000000) {
		//     echo "Sorry, your file is too large. ";
		//     $uploadOk = 0;
		// }
		// Allow certain file formats
		if ($imageFileType != "xls" && $imageFileType != "xlsx") {
            return redirect('event/'.$event_id.'/athletes')->with('error', 'Sorry, only XLS & XLSX files are allowed.');
		}
		// if everything is ok, try to upload file
	    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
	        // $msg += "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded. <br/>";
	        $result = RankingsController::importRun($target_file);
	        if ($result != false){
                return redirect('event/'.$event_id.'/athletes')->with('success', 'Excel file imported.');
	        } else {
		        $error_msg = "Sorry, there was an error uploading your file.";
                return redirect('event/'.$event_id.'/athletes')->with('error', $error_msg);
            }
	    } else {
	        $error_msg = "Sorry, there was an error uploading your file.";
            return redirect('event/'.$event_id.'/athletes')->with('error', $error_msg);
	    }
    }

	public function importRun($file) {
		// 	// echo $file,"<br/>";
		// 	require_once '../lib/Excel.php';
		// 	$data = importAsExcel($file);
        //
		// 	$atmIDs = array();
        //
		// 	foreach ($data as $row){
		// 		$athlete = array();
		// 		//Mapping athlete
		// 		$athlete['familyname'] = empty($row[5])?"":$row[5];
		// 		$athlete['givenname'] = empty($row[6])?"":$row[6];
		// 		$athlete['gender'] = empty($row[7])?"":$row[7];
		// 		$athlete['birthdate'] = empty($row[8])?"":$row[8];
		// 		$athlete['nat'] = empty($row[9])?"":$row[9];
		// 		$athlete['city'] = empty($row[11])?"":$row[11];
		// 		$athlete['team'] = empty($row[12])?"":$row[12];
		// 		$athlete['localname'] = empty($row[13])?"":$row[13];
        //
		// 		if (empty($row[17])){
		// 			$athleteid = Rankings_Model::importAthlete($athlete);
		// 		} else {
		// 			//ATM ID check
		// 			$result = Rankings_Model::checkATMID($row[17]);
		// 			if (!$result) {
		// 				$athleteid = Rankings_Model::importAthlete($athlete);
		// 			} else {
		// 				$athleteid = $row[17];
		// 			}
		// 		}
        //
		// 		$result = array();
		// 		// Mapping result
		// 		$result['athleteid'] = $athleteid;
		// 		$result['racename'] = empty($row[0])?"":$row[0];
		// 		$result['eventname'] = empty($row[1])?"":$row[1];
		// 		$result['ranking'] = empty($row[2])?"":$row[2];
		// 		$result['genpos'] = empty($row[3])?"":$row[3];
		// 		$result['time'] = empty($row[4])?"":$row[4];
		// 		$result['bib'] = empty($row[10])?"":$row[10];
		// 		$result['finishpoints'] = empty($row[14])?"":$row[14];
		// 		$result['rankingpoints'] = empty($row[15])?"":$row[15];
		// 		$result['bonuspoints'] = empty($row[16])?"":$row[16];
		// 		$result['year'] = 2018;
		// 		$data = Rankings_Model::importResult($result);
        //
		// 		Rankings_Model::importRace($result['racename'], $result['eventname'], $result['year'], $url);
		// 		// echo $data,"<br/>";
        //
		// 		$atmIDs[] = $athleteid;
		// 	}
        //
		// 	$result = insertAtmidsToExcel($file, $atmIDs);
        //
		// 	// Move file to imported folder
		// 	$target_dir = "imported/";
		// 	$target_file = $target_dir . basename($file);
		// 	rename($file, $target_file);
		// }
		// if ($result)
		// 	return TRUE;
	}

}
