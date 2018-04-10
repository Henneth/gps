<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\ReplayTracking_Model as ReplayTracking_Model;

class ReplayTrackingController extends Controller {

    public function index($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();
        $data = ReplayTracking_Model::getLocations($event_id, $event->datetime_from, $event->datetime_to);
        $array = [];
        foreach ($data as $key => $value) {
            $array[$value->device_id][] = $value;
        }
        $jsonData = json_encode($array);
        $timestamp_from = strtotime($event->datetime_from." HKT");
        echo $timestamp_from.' ';
        $timestamp_to = strtotime($event->datetime_to." HKT");
        echo $timestamp_to;
        echo 'updated';
        // return view('replay-tracking')->with(array('data' => $jsonData, 'event_id' => $event_id, 'timestamp_from' => $timestamp_from, 'timestamp_to' => $timestamp_to));
    }

}
