<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class LiveTrackingController extends Controller {

    public function index($event_id) {
        // $event = DB::table('gps_data')
            // ->join('events', 'gps_data.id', '=', 'contacts.user_id')
            // ->where('event_id', $event_id)->first();
        return view('live-tracking')->with(array('event' => $event));
    }

}
