<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\ReplayTracking_Model as ReplayTracking_Model;
use App\DeviceMapping_Model as DeviceMapping_Model;


use Auth;

class ReplayTrackingController extends Controller {

    public function index($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();

        if (Auth::check()) {
            $data = ReplayTracking_Model::getLocations($event_id, $event->datetime_from, $event->datetime_to);
            $profile = DeviceMapping_Model::getAthletesProfile($event_id);
        }else{
            $data = ReplayTracking_Model::getLocations2($event_id, $event->datetime_from, $event->datetime_to);
            $profile = DeviceMapping_Model::getAthletesProfile2($event_id);
        }

        // $data = ReplayTracking_Model::getLocations($event_id, $event->datetime_from, $event->datetime_to);
        $array = [];
        foreach ($data as $key => $value) {
            $array[$value->device_id][] = $value;
        }
        $jsonData = json_encode($array);
        $timestamp_from = strtotime($event->datetime_from." HKT");
        $timestamp_to = strtotime($event->datetime_to." HKT");

        $route = DB::table('routes')
            ->where('event_id',$event_id)
            ->select('route')
            ->first();

        $routeIndexes = (array) ReplayTracking_Model::getRouteDistance($event_id);
        $routeIndexesByDevice = $this->group_by($routeIndexes, "device_id");
        // echo "<pre>".print_r($routeIndexesByDevice,1)."</pre>";


        $routeIndexesByDevice = json_encode($routeIndexesByDevice);
        $profile = DeviceMapping_Model::getAthletesProfile($event_id);
        return view('replay-tracking')->with(array('data' => $jsonData, 'profile' => $profile, 'event_id' => $event_id, 'timestamp_from' => $timestamp_from, 'timestamp_to' => $timestamp_to, 'route' => $route, 'event'=>$event, 'routeIndexesByDevice' => $routeIndexesByDevice ));
    }

    private function group_by($array, $key) {
        $return = array();
        foreach($array as $val) {
            $return[$val->$key][] = $val;
        }
        return $return;
    }

}
