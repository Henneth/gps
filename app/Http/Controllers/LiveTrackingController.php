<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\LiveTracking_Model as LiveTracking_Model;

class LiveTrackingController extends Controller {

    public function index($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();
        $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to);
        $jsonData = json_encode($data);
        return view('live-tracking')->with(array('data' => $jsonData, 'event' => $event, 'event_id' => $event_id));
    }
    public function poll($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();
        $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to);
        return response()->json($data);
    }

}
