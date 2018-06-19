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
        shell_exec("php /Users/henneth/sites/gps/public/calculation.php 'alert' >> /Users/henneth/sites/gps/public/calculation.log");

        $event = DB::table('events')->where('event_id', $event_id)->first();
        $route = DB::table('routes')
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
            $profile = DeviceMapping_Model::getAthletesProfile($event_id);
        }else{
            $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to, false);
            foreach ($data as $value) {
                $progressData = LiveTracking_Model::getRouteDistanceByDevice($event_id, $value->device_id);
                $value->distance = !empty($progressData) ? $progressData[0]->distance : 0;
            }
            $jsonData = json_encode($data);
            // used in profile tab
            $profile = DeviceMapping_Model::getAthletesProfile2($event_id);
        }

        $jsonProfile = json_encode($profile);

        // get checkpoint times
        $getCheckpointData = (array) LiveTracking_Model::getCheckpointData($event_id);
        $tempCheckpointData = $this->group_by($getCheckpointData, 'device_id');
        $checkpointData = json_encode($tempCheckpointData);

        // get checkpoint distances
        $tempCheckpointDistances = DB::table('route_distances')->where('event_id', $event_id)->where('is_checkpoint', 1)->get();
        $checkpointDistances = json_encode($tempCheckpointDistances);

        // get athlete's distances for elevation chart
        $currentRouteIndex = LiveTracking_Model::getRouteDistance($event_id);
        $currentRouteIndex = json_encode($currentRouteIndex);

        return view('live-tracking')->with(array('data' => $jsonData, 'event' => $event, 'event_id' => $event_id, 'route' => $route, 'profile' => $profile, 'jsonProfile' => $jsonProfile, 'currentRouteIndex'=>$currentRouteIndex, 'checkpointData'=>$checkpointData, 'checkpointDistances'=>$checkpointDistances));
    }

    // automatically update data from server
    public function poll($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();

        // data for drawing map
        if (Auth::check()) {
            $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to, true);
            foreach ($data as $value) {
                $progressData = LiveTracking_Model::getRouteDistanceByDevice($event_id, $value->device_id);
                $value->distance = !empty($progressData) ? $progressData[0]->distance : 0;
            }
            $jsonData = json_encode($data);
            $profile = DeviceMapping_Model::getAthletesProfile($event_id);
        }else{
            $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to, false);
            foreach ($data as $value) {
                $progressData = LiveTracking_Model::getRouteDistanceByDevice($event_id, $value->device_id);
                $value->distance = !empty($progressData) ? $progressData[0]->distance : 0;
            }
            $jsonData = json_encode($data);
            $profile = DeviceMapping_Model::getAthletesProfile2($event_id);
        }

        // get checkpoint times
        $getCheckpointData = (array) LiveTracking_Model::getCheckpointData($event_id);
        $checkpointData = $this->group_by($getCheckpointData, 'device_id');

        // get athlete's distances for elevation chart
        $currentRouteIndex = LiveTracking_Model::getRouteDistance($event_id);

        $array = [];
        $array['data'] = $data;
        $array['checkpointData'] = $checkpointData;
        $array['currentRouteIndex'] = $currentRouteIndex;

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
