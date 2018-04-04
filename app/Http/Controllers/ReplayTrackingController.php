<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class ReplayTrackingController extends Controller {

    public function index($event_id) {
        // $data = DB::table('gps_data')->orderby('id', 'desc')->get();
        return view('replay-tracking')->with(array('event_id' => $event_id));
    }

}
