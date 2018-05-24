<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\LiveTracking_Model as LiveTracking_Model;
use App\DeviceMapping_Model as DeviceMapping_Model;
use Auth;

class LiveTrackingController extends Controller {

    public function index($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();
        $route = DB::table('routes')
            ->where('event_id',$event_id)
            ->select('route')
            ->first();

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

        // get checkpoint distance relevant
        $getCheckpointData = (array) LiveTracking_Model::getCheckpointData($event_id);
        $tempCheckpointData = $this->group_by($getCheckpointData, 'device_id');
        $checkpointData = json_encode($tempCheckpointData);
        // echo "<pre>".print_r($tempCheckpointData,1)."</pre>";

        // get athlete's distance relevant
        $currentRouteIndex = LiveTracking_Model::getRouteDistance($event_id);
        $currentRouteIndex = json_encode($currentRouteIndex);

        return view('live-tracking')->with(array('data' => $jsonData, 'event' => $event, 'event_id' => $event_id, 'route' => $route, 'profile' => $profile, 'jsonProfile' => $jsonProfile, 'currentRouteIndex'=>$currentRouteIndex, 'checkpointData'=>$checkpointData ));
    }

    // automatically update data from server
    public function poll($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();

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

        // get checkpoint distance relevant
        $getCheckpointData = (array) LiveTracking_Model::getCheckpointData($event_id);
        $checkpointData = $this->group_by($getCheckpointData, 'device_id');

        // get athlete's distance relevant
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
