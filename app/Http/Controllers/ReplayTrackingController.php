<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\ReplayTracking_Model as ReplayTracking_Model;
use App\LiveTracking_Model as LiveTracking_Model;
use App\DeviceMapping_Model as DeviceMapping_Model;


use Auth;

class ReplayTrackingController extends Controller {

    public function index($event_id) {

        // run calculation.php
        // shell_exec("php ".public_path()."/calculation.php 'alert' >> ".public_path()."/calculation.log");
        // shell_exec("php ".public_path()."/calculation.php");

        $event = DB::table('events')->where('event_id', $event_id)->first();

        if (Auth::check()) {
            $data = ReplayTracking_Model::getLocations($event_id, $event->datetime_from, $event->datetime_to, true);
            $profile = DeviceMapping_Model::getAthletesProfile($event_id);
        }else{
            $data = ReplayTracking_Model::getLocations($event_id, $event->datetime_from, $event->datetime_to, false);
            $profile = DeviceMapping_Model::getAthletesProfile2($event_id);
        }

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

        // get athlete's distances for elevation chart
        $routeIndexes = (array) ReplayTracking_Model::getRouteDistance($event_id);
        $routeIndexesByDevice = $this->group_by($routeIndexes, "device_id");

        // get checkpoint times
        $getCheckpointData = (array) LiveTracking_Model::getCheckpointData($event_id);
        $tempCheckpointData = $this->group_by($getCheckpointData, 'device_id');
        $checkpointData = json_encode($tempCheckpointData);
        // echo "<pre>".print_r($data,1)."</pre>";

        // get checkpoint distances
        $tempCheckpointDistances = DB::table('route_distances')->where('event_id', $event_id)->where('is_checkpoint', 1)->get();
        $checkpointDistances = json_encode($tempCheckpointDistances);

        $routeIndexesByDevice = json_encode($routeIndexesByDevice);
        $profile = DeviceMapping_Model::getAthletesProfile($event_id);

        echo $data;

        return view('replay-tracking')->with(array('data' => $jsonData, 'profile' => $profile, 'event_id' => $event_id, 'timestamp_from' => $timestamp_from, 'timestamp_to' => $timestamp_to, 'route' => $route, 'event'=>$event, 'routeIndexesByDevice' => $routeIndexesByDevice, 'checkpointData'=>$checkpointData, 'checkpointDistances'=>$checkpointDistances));
    }

    private function group_by($array, $key) {
        $return = array();
        foreach($array as $val) {
            $return[$val->$key][] = $val;
        }
        return $return;
    }

}
