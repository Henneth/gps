<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class LiveTrackingController extends Controller {

    public function index($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();
        $data = DB::table('gps_data')
            ->where('datetime', '>', $event->datetime_from)
            ->where('datetime', '<', $event->datetime_to)->orderBy('datetime', 'desc')->distinct()->first();
        return view('live-tracking')->with(array('data' => $data));
    }

}
