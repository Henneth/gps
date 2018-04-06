<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\LiveTracking_Model as LiveTracking_Model;

class ReplayTrackingController extends Controller {

    public function index($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();
        $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to);
        $jsonData = json_encode($data);
        $timestamp_from = strtotime($event->datetime_from);
        $timestamp_to = strtotime($event->datetime_to);
        return view('replay-tracking')->with(array('data' => $jsonData, 'event_id' => $event_id, 'timestamp_from' => $timestamp_from, 'timestamp_to' => $timestamp_to));
    }

}
