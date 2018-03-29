<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class DeviceMappingController extends Controller {

    public function index($event_id) {
        $devices = DB::table('device_mapping')->where('event_id', $event_id)->get();
        return view('device-mapping')->with(array('devices' => $devices));
    }

}
