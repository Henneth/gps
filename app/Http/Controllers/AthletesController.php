<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class AthletesController extends Controller {

    public function index($event_id) {
        $athletes = DB::table('athletes')
            ->leftJoin('countries', 'athletes.country_code', '=', 'countries.code')
            ->where('event_id', $event_id)
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
            'event_id' => $event_id,
            'bib_number' => $_POST['bib_number'],
            'first_name' => $_POST['first_name'],
            'last_name' => !empty($_POST['last_name']) ? $_POST['last_name'] : NULL,
            'zh_full_name' => !empty($_POST['zh_full_name']) ? $_POST['zh_full_name'] : NULL,
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
            'zh_full_name' => !empty($_POST['zh_full_name']) ? $_POST['zh_full_name'] : NULL,
            'country_code' => !empty($_POST['country_code']) ? $_POST['country_code'] : NULL,
            'colour_code' => !empty($_POST['colour_code']) ? $_POST['colour_code'] : NULL,
        ]);
        return redirect('event/'.$event_id.'/athletes')->with('success', 'Athlete is edited.');
    }
    public function importFromExcel($event_id) {

        $target_dir = storage_path('app/athletes/');
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

			require_once base_path().'/libs/Excel.php';
			$data = importAsExcel($target_file);
	        // print_r($data);
	        $array = [];
	        foreach($data as $temp){

	        	$array[] = array(
	        		'bib_number' => !empty($temp[0]) ? $temp[0] : NULL,
	        		'first_name' => !empty($temp[1]) ? $temp[1] : NULL,
	        		'last_name' => !empty($temp[2]) ? $temp[2] : NULL,
	        		'zh_full_name' => !empty($temp[3]) ? $temp[3] : NULL,
	        		'country_code' => !empty($temp[4]) ? $temp[4] : NULL,
	        		'colour_code' => !empty($temp[5]) ? $temp[5] : NULL
	        	);

	        }
        	DB::table('athletes')->insert($array);

            return redirect('event/'.$event_id.'/athletes')->with('success', 'Excel file imported.');

	    } else {
	        $error_msg = "Sorry, there was an error uploading your file.";
            return redirect('event/'.$event_id.'/athletes')->with('error', $error_msg);
	    }
    }

}
