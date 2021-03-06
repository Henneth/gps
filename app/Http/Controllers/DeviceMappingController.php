<?php

namespace App\Http\Controllers;
use DateTime;
use DB;
use App\Http\Controllers\Controller;
use App\DeviceMapping_Model as DeviceMapping_Model;


class DeviceMappingController extends Controller {

    public function index($event_id) {
        $devices = DeviceMapping_Model::getDeviceMappings($event_id);
        // $devices = DB::table('device_mapping')
        //     ->leftjoin('athletes', 'athletes.bib_number', '=', 'device_mapping.bib_number' )
        //     ->where('device_mapping.event_id', $event_id)
        //     ->where('athletes.event_id', $event_id)
        //     ->orderby('device_mapping_id', 'desc')
        //     ->get();
        // print_r($devices);
        $athletes = DB::table('gps_live_'.$event_id.'.athletes')->get();

        $is_live = DB::table('events')
            ->select('event_type', 'live')
        	->where('event_id',$event_id)
        	->first();
        // echo '<pre>'.print_r($devices, 1).'</pre>';
        return view('device-mapping')->with(array('devices' => $devices, 'event_id' => $event_id, 'athletes' => $athletes, 'is_live' => $is_live->live));
    }


    public function addDeviceMapping($event_id) {
        if (empty($_POST['device_id']) || empty($_POST['bib_number'])) {
            return redirect('event/'.$event_id.'/device-mapping')->with('error', 'Device ID and Bib Number must not be empty.');
        }

        // check duplicate
        $mapping = DB::table('gps_live_'.$event_id.'.device_mapping')
            ->where('device_id', $_POST['device_id'])
            ->first();
        if (!empty($mapping)) {
            return redirect('event/'.$event_id.'/device-mapping')->with('error', 'Device ID has already mapped.');
        }

        DB::table('gps_live_'.$event_id.'.device_mapping')->insert([
            'device_id' => $_POST['device_id'],
            'bib_number' => $_POST['bib_number'],
            'start_time' => !empty($_POST['start_time']) ? $_POST['start_time'] : NULL,
            'end_time' => !empty($_POST['end_time']) ? $_POST['end_time'] : NULL
        ]);

        return redirect('event/'.$event_id.'/device-mapping')->with('success', 'Device and athlete is mapped.');
    }


    public function editDeviceMapping($event_id) {
        if (empty($_POST['device_id']) || empty($_POST['bib_number'])) {
            return redirect('event/'.$event_id.'/device-mapping')->with('error', 'Device ID and Bib Number must not be empty.');
        }

        DB::table('gps_live_'.$event_id.'.device_mapping')
            ->where('device_mapping_id', $_POST['device_mapping_id'])
            ->update([
                'device_id' => $_POST['device_id'],
                'bib_number' => $_POST['bib_number'],
                'start_time' => !empty($_POST['start_time']) ? $_POST['start_time'] : NULL,
                'end_time' => !empty($_POST['end_time']) ? $_POST['end_time'] : NULL
            ]);

        return redirect('event/'.$event_id.'/device-mapping')->with('success', 'Mapping is edited.');
    }
    // public function addDeviceMapping($event_id) {
    //     if (empty($_POST['device_id'])) {
    //         return redirect('event/'.$event_id.'/device-mapping')->with('error', 'Device ID must not be empty.');
    //     }
    //
    //     $device_id = $_POST['device_id'];
    //     $bib_number = $_POST['bib_number'];
    //     $first_name = $_POST['first_name'];
    //     $last_name = $_POST['last_name'];
    //     $country_code = $_POST['country_code'];
    //     $colour_code = $_POST['colour_code'];
    //     $status = $_POST['status'];
    //
    //     DB::table('athletes')->insert(
    //         ['bib_number' => $bib_number, 'first_name' => $first_name, 'last_name' => $last_name, 'country_code' => $country_code, 'colour_code' => $colour_code]
    //     );
    //
    //     $athlete_id = DB::getPdo()->lastInsertId();
    //
    //     DB::table('device_mapping')->insert(
    //         ['device_id' => $device_id, 'event_id' => $event_id, 'athlete_id' => $athlete_id, 'status' => $status]
    //     );
    //     return redirect('event/'.$event_id.'/device-mapping')->with('success', 'Mapping added.');
    // }

    public function importFromExcel($event_id) {

        $target_dir = storage_path('app/device_mapping/');
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Check if file already exists
        if (file_exists($target_file)) {
            return redirect('event/'.$event_id.'/device-mapping')->with('error', 'Excel file with the same name has been uploaded.');
        }
        // Check file size
        // if ($_FILES["fileToUpload"]["size"] > 500000000) {
        //     echo "Sorry, your file is too large. ";
        //     $uploadOk = 0;
        // }
        // Allow certain file formats
        if ($imageFileType != "xls" && $imageFileType != "xlsx") {
            return redirect('event/'.$event_id.'/device-mapping')->with('error', 'Sorry, only XLS & XLSX files are allowed.');
        }
        // if everything is ok, try to upload file
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            // $msg += "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded. <br/>";

            require_once base_path().'/libs/Excel.php';
            $data = importAsExcel($target_file);


            // check duplicate
            $device_ids_sql = DB::table('gps_live_'.$event_id.'.device_mapping')
                ->select('device_id')
                ->get();

            $device_ids = [];
            foreach ($device_ids_sql as $temp) {
                $device_ids[] = $temp->device_id;
            }
// // bib_number can repeat, but device_id cannot
//             // check duplicate
//             $bib_numbers_sql = DB::table('gps_live_'.$event_id.'.device_mapping')
//                 ->select('bib_number')
//                 ->get();
//
//             $bib_numbers = [];
//             foreach ($bib_numbers_sql as $temp) {
//                 $bib_numbers[] = $temp->bib_number;
//             }

            // print_r($data);
            $errors= [];
            $array = [];
            $count = 1;
            foreach($data as $temp){
                $hasError = false;
                $startTime3 = NULL;
                $endTime3 = NULL;

                // if (!empty($temp[4]) && $temp[4] == '1') {
                //     $status = "visible";
                // } else {
                //     $status = "hidden";
                // }


                // create DateTime object from timestamp
                if( !empty($temp[2]) ){
                    $sOrigin = $temp[2];
                    list($whole, $decimal) = explode('.', $sOrigin);
                    $sInteger = intval($whole); // Integer
                    $startTime = $this->convertDate($sInteger);
                    $startTime2 = convertTime($sOrigin);
                    $startTime3 = $startTime .' '. $startTime2;
                }

                if( !empty($temp[3]) ){
                    $eOrigin = $temp[3];
                    list($whole, $decimal) = explode('.', $eOrigin);
                    $eInteger = intval($whole); // Integer
                    $endTime = $this->convertDate($eInteger);
                    $endTime2 = convertTime($eOrigin);
                    $endTime3 = $endTime .' '. $endTime2;
                }

                if( !empty($temp[0]) && in_array($temp[0], $device_ids) ){
                    $errors[] = "#".$count." - Device ID \"$temp[0]\" already exists!";
                    $hasError = true;
                }

                // else if( !empty($temp[1]) && in_array($temp[1], $bib_numbers) ){
                //     $errors[] = "#".$count." - Bib Number \"$temp[1]\" already exists!";
                //     $hasError = true;
                // }
                else if ( empty($temp[1]) ){
                        $errors[] = "#".$count." - Bib Number \"$temp[1]\" must not be empty!";
                        $hasError = true;
                }

                if (!$hasError) {
                    $bib_number = $temp[1];

                    $array[] = array(
                        'device_id' => $temp[0],
                        'bib_number' => $temp[1],
                        'start_time' => !empty($startTime3) ? $startTime3 : NULL,
                        'end_time' => !empty($endTime3) ? $endTime3 : NULL,
                        // 'status' => $status
                    );
                }

                $count++;
            }

            // print_r($array);
            DB::table('gps_live_'.$event_id.'.device_mapping')->insert($array);

            return redirect('event/'.$event_id.'/device-mapping')->with('success', count($array).' '.'records have been imported.')
            ->with('errors', $errors);;

        } else {
            $error_msg = "Sorry, there was an error uploading your file.";
            return redirect('event/'.$event_id.'/device-mapping')->with('error', $error_msg);
        }
    }


    private function convertDate($dateValue) {
      $unixDate = ($dateValue - 25569) * 86400;
      return gmdate("Y-m-d", $unixDate);
    }

}
