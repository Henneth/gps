<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class AthletesController extends Controller {

    public function index($event_id) {
        $athletes = DB::table('gps_live_'.$event_id.'.athletes')
            ->leftJoin('countries', 'athletes.country_code', '=', 'countries.code')
            ->orderby(DB::raw('ABS(bib_number)'), 'desc')
            ->get();

        $countries = DB::table('gps_live_'.$event_id.'.countries')
        	->orderby('country','ASC')
            ->get();

        $is_live = DB::table('events')
            ->select('live')
            ->where('event_id',$event_id)
            ->first();

        return view('athletes')->with(array('athletes' => $athletes, 'event_id' => $event_id, 'countries' => $countries, 'is_live' => $is_live->live));
    }


    public function addAthlete($event_id) {
        if (empty($_POST['bib_number']) || empty($_POST['first_name'])) {
            return redirect('event/'.$event_id.'/athletes')->with('error', 'Bib number and first name must not be empty.');
        }

        // check duplicate
        $bib_number = DB::table('gps_live_'.$event_id.'.athletes')
            ->where('bib_number', $_POST['bib_number'])
            ->first();
        if (!empty($bib_number)){
            return redirect('event/'.$event_id.'/athletes')->with('error', 'Bib number exists already.');
        }

        // echo '<pre>'.print_r($_POST, 1).'</pre>';
        DB::table('gps_live_'.$event_id.'.athletes')->insert([
            'bib_number' => $_POST['bib_number'],
            'first_name' => $_POST['first_name'],
            'last_name' => !empty($_POST['last_name']) ? $_POST['last_name'] : NULL,
            'zh_full_name' => !empty($_POST['zh_full_name']) ? $_POST['zh_full_name'] : NULL,
            'is_public' => (!empty($_POST['is_public']) && $_POST['is_public'] == "on") ? 1 : 0,
            'status' => !empty($_POST['status']) ? $_POST['status'] : 'visible',
            'country_code' => !empty($_POST['country_code']) ? $_POST['country_code'] : NULL,
            'gender' => !empty($_POST['gender']) ? $_POST['gender'] : NULL,
            'category' => !empty($_POST['category']) ? $_POST['category'] : NULL,
        ]);

        return redirect('event/'.$event_id.'/athletes')->with('success', 'Athlete is added.');
    }

    public function deleteAthlete($event_id) {
        DB::table('gps_live_'.$event_id.'.athletes')
            ->where('bib_number', $_POST['del_athlete'])
            ->delete();
        return redirect('event/'.$event_id.'/athletes')->with('success', 'Athlete has been deleted.');
    }


    public function editAthlete($event_id) {
        $is_live = DB::table('events')
            ->select('live')
            ->where('event_id',$event_id)
            ->first();

        if ( (!$is_live->live && empty($_POST['new_bib_number'])) || empty($_POST['first_name'])) {
            return redirect('event/'.$event_id.'/athletes')->with('error', 'Bib number and first name must not be empty.');
        }

        $arr = [];
        if (!$is_live->live) {
            $arr['bib_number'] = $_POST['new_bib_number'];
        }
        $arr['first_name'] = $_POST['first_name'];
        $arr['last_name'] = !empty($_POST['last_name']) ? $_POST['last_name'] : NULL;
        $arr['zh_full_name'] = !empty($_POST['zh_full_name']) ? $_POST['zh_full_name'] : NULL;
        $arr['is_public'] = (!empty($_POST['is_public']) && $_POST['is_public'] == "on") ? 1 : 0;
        $arr['status'] = !empty($_POST['status']) ? $_POST['status'] : 'visible';
        $arr['country_code'] = !empty($_POST['country_code']) ? $_POST['country_code'] : NULL;
        $arr['gender'] = !empty($_POST['gender']) ? $_POST['gender'] : NULL;
        $arr['category'] = !empty($_POST['category']) ? $_POST['category'] : NULL;
        DB::table('gps_live_'.$event_id.'.athletes')
            ->where('bib_number', $_POST['old_bib_number'])
            ->update($arr);

        if ($is_live->live) {
            DB::transaction(function () use($event_id) {
                DB::table('gps_live_'.$event_id.'.participants')->truncate();

                $live_db = "gps_live_{$event_id}";
        		DB::insert("INSERT INTO {$live_db}.participants (bib_number, first_name, last_name, zh_full_name, gender, category, is_public, country_code, country, country_zh_hk, start_time, end_time, status) SELECT bib_number, first_name, last_name, zh_full_name, gender, category, is_public, country_code, country, country_zh_hk, null, null, status FROM {$live_db}.athletes AS a LEFT JOIN {$live_db}.countries AS c ON a.country_code = c.code");
            });

        }

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
            $countries_sql = DB::table('gps_live_'.$event_id.'.countries')
                ->select('code')
                ->get();

            $countries = [];
            foreach ($countries_sql as $key => $temp) {
                $countries[] = $temp->code;
            }

            $bib_numbers_sql = DB::table('gps_live_'.$event_id.'.athletes')
                ->select('bib_number')
                ->get();

            $bib_numbers = [];
            foreach ($bib_numbers_sql as $key => $temp) {
                $bib_numbers[] = $temp->bib_number;
            }
            // print_r($bib_numbers);
            $errors = [];
	        $array = [];
            $count = 1;
	        foreach($data as $temp){
                $hasError = false;
                if(!empty($temp[0]) && in_array($temp[0], $bib_numbers) ){
                    $errors[] = "#".$count." - Bib number \"$temp[0]\" already exists!";
                    $hasError = true;
                }
                else if(!empty($temp[4]) && !in_array($temp[4], $countries) ){
                    $errors[] = "#".$count." - Country code \"$temp[4]\" is not vaild!";
                    $hasError = true;
                }

                if (!empty($temp[5]) && $temp[5] == '1') {
                    $status = "visible";
                } else {
                    $status = "hidden";
                }

                if (!empty($temp[6]) && $temp[6] == 'M') {
                    $gender = "M";
                } else if (!empty($temp[6]) && $temp[6] == 'F') {
                    $gender = "F";
                } else {
                    $gender = NULL;
                }

                // else if (!empty($temp[5]) && !preg_match('/^[a-f0-9]{6}$/i', $temp[5])) { //hex color is valid
                //     $errors[] = "#".$count." - Color code \"$temp[5]\" is not vaild!";
                //     $hasError = true;
                // }

                if (!$hasError){
    	        	$array[] = array(
    	        		'bib_number' => !empty($temp[0]) ? $temp[0] : NULL,
    	        		'first_name' => !empty($temp[1]) ? $temp[1] : NULL,
    	        		'last_name' => !empty($temp[2]) ? $temp[2] : NULL,
    	        		'zh_full_name' => !empty($temp[3]) ? $temp[3] : NULL,
    	        		'country_code' => !empty($temp[4]) ? $temp[4] : NULL,
    	        		'category' => !empty($temp[7]) ? $temp[7] : NULL,
                        'gender' => $gender,
                        'status' => $status
    	        		// 'colour_code' => !empty($temp[5]) ? $temp[5] : NULL
    	        	);
                }

                $count++;
	        }
        	DB::table('gps_live_'.$event_id.'.athletes')->insert($array);
            return redirect('event/'.$event_id.'/athletes')->with('success', count($array).' '.'records have been imported.')
            ->with('errors', $errors);
	    } else {
	        $error_msg = "Sorry, there was an error uploading your file.";
            return redirect('event/'.$event_id.'/athletes')->with('error', $error_msg);
	    }
    }

}
