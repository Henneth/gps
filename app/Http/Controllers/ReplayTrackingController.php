<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\ReplayTracking_Model as ReplayTracking_Model;
use App\DeviceMapping_Model as DeviceMapping_Model;


use Auth;

class ReplayTrackingController extends Controller {

    // public function index($event_id) {
    //
    //     // run calculation.php
    //     // shell_exec("php ".public_path()."/calculation.php 'alert' >> ".public_path()."/calculation.log");
    //     shell_exec("php ".public_path()."/calculation.php replay ".$event_id);
    //
    //     $event = DB::table('events')->where('event_id', $event_id)->first();
    //
    //     if (Auth::check()) {
    //         $data = ReplayTracking_Model::getLocations($event_id, $event->datetime_from, $event->datetime_to, true);
    //         $profile = DeviceMapping_Model::getAthletesProfile($event_id, true);
    //     }else{
    //         $data = ReplayTracking_Model::getLocations($event_id, $event->datetime_from, $event->datetime_to, false);
    //         $profile = DeviceMapping_Model::getAthletesProfile($event_id, false);
    //     }
    //
    //     $array = [];
    //     foreach ($data as $key => $value) {
    //         $array[$value->device_id][] = $value;
    //     }
    //     // echo "<pre>".print_r($array,1)."</pre>";
    //
    //     $jsonData = json_encode($array);
    //     $timestamp_from = strtotime($event->datetime_from." HKT");
    //     $timestamp_to = strtotime($event->datetime_to." HKT");
    //
    //     $route = DB::table('routes')
    //         ->where('event_id',$event_id)
    //         ->select('route')
    //         ->first();
    //
    //     // get athlete's distances for elevation chart
    //     $routeIndexes = (array) ReplayTracking_Model::getRouteDistance($event_id);
    //     $routeIndexesByDevice = $this->group_by($routeIndexes, "device_id");
    //
    //     // get checkpoint times
    //     $getCheckpointData = (array) ReplayTracking_Model::getCheckpointData($event_id);
    //     $tempCheckpointData = $this->group_by($getCheckpointData, 'device_id');
    //     $checkpointData = json_encode($tempCheckpointData);
    //     // echo "<pre>".print_r($event,1)."</pre>";
    //
    //     // get checkpoint distances
    //     $tempCheckpointDistances = DB::table('route_distances')->where('event_id', $event_id)->where('is_checkpoint', 1)->get();
    //     $checkpointDistances = json_encode($tempCheckpointDistances);
    //
    //     $routeIndexesByDevice = json_encode($routeIndexesByDevice);
    //
    //
    //     return view('replay-tracking')->with(array('data' => $jsonData, 'profile' => $profile, 'event_id' => $event_id, 'timestamp_from' => $timestamp_from, 'timestamp_to' => $timestamp_to, 'route' => $route, 'event'=>$event, 'routeIndexesByDevice' => $routeIndexesByDevice, 'checkpointData'=>$checkpointData, 'checkpointDistances'=>$checkpointDistances));
    // }

    public function index($event_id) {

        $time_start = microtime(true);

        // run calculation.php
        // shell_exec("php ".public_path()."/calculation.php 'alert' >> ".public_path()."/calculation.log");
        // shell_exec("php ".public_path()."/calculation.php replay ".$event_id);

        $event = DB::table('events')->where('event_id', $event_id)->first();


        // $array = [];
        // foreach ($data as $key => $value) {
        //     $array[$value->device_id][] = $value;
        // }
        // echo "<pre>".print_r($array,1)."</pre>";

        // $jsonData = json_encode($array);
        $timestamp_from = strtotime($event->datetime_from." HKT");
        $timestamp_to = strtotime($event->datetime_to." HKT");

        $route = DB::table('routes')
            ->where('event_id',$event_id)
            ->select('route')
            ->first();

//  - -- - -- - -- - -- -- - -- -- - -- -- - -
        // to-do tasks, improve the processing time of data
        // refer to poll function below, filter by device_id

        // get athlete's distances for elevation chart
        // $routeIndexes = (array) ReplayTracking_Model::getRouteDistance($event_id);
        // $routeIndexesByDevice = $this->group_by($routeIndexes, "device_id");
        // // echo "<pre>".print_r($routeIndexesByDevice,1)."</pre>";
        //
        // $routeIndexesByDevice = json_encode($routeIndexesByDevice);

//  - -- - -- - -- - -- -- - -- -- - -- -- - -


        // get checkpoint times
        // $getCheckpointData = (array) ReplayTracking_Model::getCheckpointData($event_id);
        // $tempCheckpointData = $this->group_by($getCheckpointData, 'device_id');
        // $checkpointData = json_encode($tempCheckpointData);
        // echo "<pre>".print_r($tempCheckpointData,1)."</pre>";

        // get checkpoint distances
        $tempCheckpointDistances = DB::table('route_distances')->where('event_id', $event_id)->where('is_checkpoint', 1)->get();
        $checkpointDistances = json_encode($tempCheckpointDistances);

        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        // echo $execution_time;

        if (!empty($_GET['tab']) && $_GET['tab'] == 2) {
            if (Auth::check()) {
                $profile = DeviceMapping_Model::getAthletesProfile($event_id, true, false);
            }else{
                $profile = DeviceMapping_Model::getAthletesProfile($event_id, false, false);
                // echo "<pre>".print_r($profile,1)."</pre>";
            }

            return view('replay-tracking-athletes')->with(array('profile' => $profile, 'event_id' => $event_id, 'event'=>$event));
        }

        if (!empty($_GET['tab']) && $_GET['tab'] == 1) {
            return view('replay-tracking-chart')->with(array('event_id' => $event_id, 'timestamp_from' => $timestamp_from, 'timestamp_to' => $timestamp_to, 'route' => $route, 'event'=>$event, 'checkpointDistances'=>$checkpointDistances));
        }

        else {
            return view('replay-tracking-map')->with(array('event_id' => $event_id, 'timestamp_from' => $timestamp_from, 'timestamp_to' => $timestamp_to, 'route' => $route, 'event'=>$event, 'checkpointDistances'=>$checkpointDistances));
        }
    }

    public function poll($event_id){
        $event = DB::table('events')->where('event_id', $event_id)->first();

        if ( !empty($_GET['device_ids']) ){
            $deviceIDs = json_decode($_GET['device_ids']);
            $data = [];
            foreach ($deviceIDs as $key => $deviceID) {
                $deviceData = ReplayTracking_Model::getLocationsViaDeviceID($event_id, $event->datetime_from, $event->datetime_to, $deviceID);
                $data[$deviceID] = $deviceData;
            }
        } else {
            // get 20 athletes from db
            if (Auth::check()){
                $deviceIDs = DeviceMapping_Model::getAthletesProfile($event_id, true, true);
            } else {
                $deviceIDs = DeviceMapping_Model::getAthletesProfile($event_id, false, true);
            }
            $data = [];
            foreach ($deviceIDs as $key => $deviceID) {
                $deviceData = ReplayTracking_Model::getLocationsViaDeviceID($event_id, $event->datetime_from, $event->datetime_to, $deviceID->device_id);
                $data[$deviceID->device_id] = $deviceData;
            }
        }
        return response()->json($data);
    }

    private function group_by($array, $key) {
        $return = array();
        foreach($array as $val) {
            $return[$val->$key][] = $val;
        }
        return $return;
    }

}
