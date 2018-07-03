<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\LiveTracking_Model as LiveTracking_Model;
use App\DeviceMapping_Model as DeviceMapping_Model;
use Auth;

class LiveTrackingController extends Controller {

    public function index($event_id) {

        // run calculation.php
        // shell_exec("php ".public_path()."/calculation.php 'alert' >> ".public_path()."/calculation.log");
        shell_exec("php ".public_path()."/calculation.php");

        $event = DB::table('gps_live.events')->where('event_id', $event_id)->first();
        $route = DB::table('gps_live.routes')
            ->where('event_id',$event_id)
            ->select('route')
            ->first();

        // data for drawing map
        if (Auth::check()) {
            $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to, true);
            foreach ($data as $value) {
                $progressData = LiveTracking_Model::getRouteDistanceByDevice($event_id, $value->device_id);
                $value->distance = !empty($progressData) ? $progressData[0]->distance : 0;
            }
            $jsonData = json_encode($data);
            // used in profile tab
            $profile = DeviceMapping_Model::getAthletesProfile($event_id, true);
        }else{
            $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to, false);
            foreach ($data as $value) {
                $progressData = LiveTracking_Model::getRouteDistanceByDevice($event_id, $value->device_id);
                $value->distance = !empty($progressData) ? $progressData[0]->distance : 0;
            }
            $jsonData = json_encode($data);
            // used in profile tab
            $profile = DeviceMapping_Model::getAthletesProfile2($event_id, true);
        }

        $jsonProfile = json_encode($profile);

        // get checkpoint times
        $getCheckpointData = (array) LiveTracking_Model::getCheckpointData($event_id);
        $tempCheckpointData = $this->group_by($getCheckpointData, 'device_id');
        $checkpointData = json_encode($tempCheckpointData);

        // get min time of checkpoints
        $getMinTime = LiveTracking_Model::getMinTime($event_id);
        $getMinTime = json_encode($getMinTime);

        // get checkpoint distances
        $tempCheckpointDistances = DB::table('gps_live.route_distances')->where('event_id', $event_id)->where('is_checkpoint', 1)->get();
        $checkpointDistances = json_encode($tempCheckpointDistances);

        // get athlete's distances for elevation chart
        $currentRouteIndex = LiveTracking_Model::getRouteDistance($event_id);
        $currentRouteIndex = json_encode($currentRouteIndex);

        // get athletes who reached at the end point
        $getFinishedAthletesTemp = LiveTracking_Model::getFinishedAthletes($event_id);
        $getFinishedAthletes = json_encode($getFinishedAthletesTemp);
        // echo "<pre>".print_r($getFinishedAthletes,1)."</pre>";

        // Event Type & Tail
        $event_type = DB::table('events')
            ->where('event_id',$event_id)
            ->select('event_type')
            ->first();
        if ($event_type->event_type != 'fixed route'){
            // get data within 10 minutes for tail
            $tailTemp = (array) LiveTracking_Model::getTail($event_id);
            $tail = $this->group_by($tailTemp, 'device_id');
            $tail = json_encode($tail);
        } else {
            $tail = '[]';
        }


        return view('live-tracking')->with(array('data'=>$jsonData, 'event'=>$event, 'event_id'=>$event_id, 'route' => $route, 'profile'=> $profile, 'jsonProfile'=>$jsonProfile, 'currentRouteIndex'=>$currentRouteIndex, 'checkpointData'=>$checkpointData, 'checkpointDistances'=>$checkpointDistances, 'minTime'=>$getMinTime, 'getFinishedAthletes'=>$getFinishedAthletes, 'tail'=>$tail, 'event_type'=>$event_type));
    }

    // automatically update data from server
    public function poll($event_id) {

        // run calculation.php
        // shell_exec("php ".public_path()."/calculation.php 'alert' >> ".public_path()."/calculation.log");
        shell_exec("php ".public_path()."/calculation.php > /dev/null 2>/dev/null &");

        $event = DB::table('gps_live.events')->where('event_id', $event_id)->first();

        // data for drawing map
        if (Auth::check()) {
            $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to, true);
            foreach ($data as $value) {
                $progressData = LiveTracking_Model::getRouteDistanceByDevice($event_id, $value->device_id);
                $value->distance = !empty($progressData) ? $progressData[0]->distance : 0;
            }
            $jsonData = json_encode($data);
            $profile = DeviceMapping_Model::getAthletesProfile($event_id, true);
        }else{
            $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to, false);
            foreach ($data as $value) {
                $progressData = LiveTracking_Model::getRouteDistanceByDevice($event_id, $value->device_id);
                $value->distance = !empty($progressData) ? $progressData[0]->distance : 0;
            }
            $jsonData = json_encode($data);
            $profile = DeviceMapping_Model::getAthletesProfile2($event_id, true);
        }

        // get checkpoint times
        $getCheckpointData = (array) LiveTracking_Model::getCheckpointData($event_id);
        $checkpointData = $this->group_by($getCheckpointData, 'device_id');

        // get athlete's distances for elevation chart
        $currentRouteIndex = LiveTracking_Model::getRouteDistance($event_id);

        // Event Type & Tail
        $event_type = DB::table('events')
            ->where('event_id',$event_id)
            ->select('event_type')
            ->first();
        if ($event_type->event_type != 'fixed route'){
            // get data within 10 minutes for tail
            $tailTemp = (array) LiveTracking_Model::getTail($event_id);
            $tail = $this->group_by($tailTemp, 'device_id');
        } else {
            $tail = '[]';
        }

        $array = [];
        $array['data'] = $data;
        $array['checkpointData'] = $checkpointData;
        $array['currentRouteIndex'] = $currentRouteIndex;
        $array['tail'] = $tail;

        // echo "<pre>".print_r($array,1)."</pre>";

        return response()->json($array);
    }

    // group data
    private function group_by($array, $key) {
        $return = array();
        foreach($array as $val) {
            $return[$val->$key][] = $val;
        }
        return $return;
    }


}
