<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class DeviceMappingController extends Controller {

    public function index($event_id) {
        $devices = DB::table('device_mapping')
            ->join('athletes', 'athletes.athlete_id', '=', 'device_mapping.athlete_id')
            ->where('event_id', $event_id)
            ->orderby('device_mapping_id', 'desc')
            ->get();
        $athletes = DB::table('athletes')->get();
        return view('device-mapping')->with(array('devices' => $devices, 'event_id' => $event_id, 'athletes' => $athletes));
    }
    public function addDeviceMapping($event_id) {
        if (empty($_POST['device_id']) || empty($_POST['athlete_id'])) {
            return redirect('event/'.$event_id.'/device-mapping')->with('error', 'Device ID and Athlete ID must not be empty.');
        }

        $device_id = $_POST['device_id'];
        $athlete_id = $_POST['athlete_id'];
        $status = !empty($_POST['status']) ? $_POST['status'] : 'visible';

        DB::table('device_mapping')->insert(
            ['device_id' => $device_id, 'event_id' => $event_id, 'athlete_id' => $athlete_id, 'status' => $status]
        );
        return redirect('event/'.$event_id.'/device-mapping')->with('success', 'Mapping is added.');
    }
    public function editDeviceMapping($event_id) {
        if (empty($_POST['device_id']) || empty($_POST['athlete_id'])) {
            return redirect('event/'.$event_id.'/device-mapping')->with('error', 'Device ID and Athlete ID must not be empty.');
        }

        $device_mapping_id = $_POST['device_mapping_id'];
        $device_id = $_POST['device_id'];
        $athlete_id = $_POST['athlete_id'];
        $status = !empty($_POST['status']) ? $_POST['status'] : 'visible';

        DB::table('device_mapping')->where('device_mapping_id', $device_mapping_id)->update(
            ['device_id' => $device_id, 'athlete_id' => $athlete_id, 'status' => $status]
        );
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

}
