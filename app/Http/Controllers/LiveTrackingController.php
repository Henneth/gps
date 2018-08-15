<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\LiveTracking_Model as LiveTracking_Model;
use App\DeviceMapping_Model as DeviceMapping_Model;
use Auth;

class LiveTrackingController extends Controller {

    // public function index($event_id) {
    //
    //     // run calculation.php
    //     // shell_exec("php ".public_path()."/calculation.php 'alert' >> ".public_path()."/calculation.log");
    //     shell_exec("php ".public_path()."/calculation.php");
    //
    //     $event = DB::table('gps_live.events')->where('event_id', $event_id)->first();
    //     $route = DB::table('gps_live.routes')
    //         ->where('event_id',$event_id)
    //         ->select('route')
    //         ->first();
    //
    //     // data for drawing map
    //     if (Auth::check()) {
    //         $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to, true);
    //         foreach ($data as $value) {
    //             $progressData = LiveTracking_Model::getRouteDistanceByDevice($event_id, $value->device_id);
    //             $value->distance = !empty($progressData) ? $progressData[0]->distance : 0;
    //         }
    //         $jsonData = json_encode($data);
    //         // used in profile tab
    //         $profile = DeviceMapping_Model::getAthletesProfile($event_id, true, false, true);
    //     }else{
    //         $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to, false);
    //         foreach ($data as $value) {
    //             $progressData = LiveTracking_Model::getRouteDistanceByDevice($event_id, $value->device_id);
    //             $value->distance = !empty($progressData) ? $progressData[0]->distance : 0;
    //         }
    //         $jsonData = json_encode($data);
    //         // used in profile tab
    //         $profile = DeviceMapping_Model::getAthletesProfile($event_id, false, false, true);
    //     }
    //
    //     $jsonProfile = json_encode($profile);
    //
    //     // get checkpoint times
    //     $getCheckpointData = (array) LiveTracking_Model::getCheckpointData($event_id);
    //     $tempCheckpointData = $this->group_by($getCheckpointData, 'device_id');
    //     $checkpointData = json_encode($tempCheckpointData);
    //
    //     // get min time of checkpoints
    //     $getMinTime = LiveTracking_Model::getMinTime($event_id);
    //     $getMinTime = json_encode($getMinTime);
    //
    //     // get checkpoint distances
    //     $tempCheckpointDistances = DB::table('gps_live.route_distances')->where('event_id', $event_id)->where('is_checkpoint', 1)->get();
    //     $checkpointDistances = json_encode($tempCheckpointDistances);
    //
    //     // get athlete's distances for elevation chart
    //     $currentRouteIndex = LiveTracking_Model::getRouteDistance($event_id);
    //     $currentRouteIndex = json_encode($currentRouteIndex);
    //
    //     // get athletes who reached at the end point
    //     $getFinishedAthletesTemp = LiveTracking_Model::getFinishedAthletes($event_id);
    //     $getFinishedAthletes = json_encode($getFinishedAthletesTemp);
    //     // echo "<pre>".print_r($jsonData,1)."</pre>";
    //
    //     // Event Type & Tail
    //     $event_type = DB::table('events')
    //         ->where('event_id',$event_id)
    //         ->select('event_type')
    //         ->first();
    //     if ($event_type->event_type != 'fixed route'){
    //         // get data within 10 minutes for tail
    //         $tailTemp = (array) LiveTracking_Model::getTail($event_id);
    //         $tail = $this->group_by($tailTemp, 'device_id');
    //         $tail = json_encode($tail);
    //     } else {
    //         $tail = '[]';
    //     }
    //
    //
    //     return view('live-tracking')->with(array('data'=>$jsonData, 'event'=>$event, 'event_id'=>$event_id, 'route' => $route, 'profile'=> $profile, 'jsonProfile'=>$jsonProfile, 'currentRouteIndex'=>$currentRouteIndex, 'checkpointData'=>$checkpointData, 'checkpointDistances'=>$checkpointDistances, 'minTime'=>$getMinTime, 'getFinishedAthletes'=>$getFinishedAthletes, 'tail'=>$tail, 'event_type'=>$event_type));
    // }

    public function index($event_id) {

        // run calculation.php
        // shell_exec("php ".public_path()."/calculation.php 'alert' >> ".public_path()."/calculation.log");
        // shell_exec("php ".public_path()."/calculation.php");

        $event = DB::table('events')->where('event_id', $event_id)->first();
        $route = DB::table('gps_live_'.$event_id.'.map_point')->get();
        $tempCheckpoint = DB::table('gps_live_'.$event_id.'.checkpoint')->get();

        $route = json_encode($route);
        // get checkpoint distances
        $checkpoint = json_encode($tempCheckpoint);
        // echo "<pre>".print_r($route,1)."</pre>";


        if (!empty($_GET['tab']) && $_GET['tab'] == 2) {
            if (Auth::check()) {
                $profile = DeviceMapping_Model::getAthletesProfile($event_id, true, false, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
            } else{
                $profile = DeviceMapping_Model::getAthletesProfile($event_id, false, false, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
            }

            return view('live-tracking-athletes')->with(array('profile' => $profile, 'event_id' => $event_id, 'event'=>$event));
        }

        if (!empty($_GET['tab']) && $_GET['tab'] == 1) {

            return view('live-tracking-chart')->with(array('event_id' => $event_id, 'event'=>$event, 'route' => $route, 'checkpoint'=>$checkpoint));
        }

        if (!empty($_GET['tab']) && $_GET['tab'] == 3) {
            // echo "<pre>".print_r($tempCheckpoint,1)."</pre>";

            return view('live-tracking-checkpoint')->with(array('event_id' => $event_id, 'event'=>$event, 'checkpoint'=>$tempCheckpoint));
        }

        else {
            return view('live-tracking-map')->with(array('event'=>$event, 'event_id'=>$event_id, 'route' => $route, 'checkpoint'=>$checkpoint));
        }
    }

// -----------------------------------------


// // working on this
//         // get checkpoint times
//         $getCheckpointData = (array) LiveTracking_Model::getCheckpointData($event_id);
//         $tempCheckpointData = $this->group_by($getCheckpointData, 'device_id');
//         $checkpointData = json_encode($tempCheckpointData);
//         // echo "<pre>".print_r($tempCheckpointData,1)."</pre>";



        // // Tail
        // if ($event->event_type != 'fixed route'){
        //     // get data within 10 minutes for tail
        //     $tailTemp = (array) LiveTracking_Model::getTail($event_id);
        //     $tail = $this->group_by($tailTemp, 'device_id');
        //     $tail = json_encode($tail);
        // } else {
        //     $tail = '[]';
        // }
// -----------------------------------------



        // return view('live-tracking')->with(array('data'=>$jsonData, 'event'=>$event, 'event_id'=>$event_id, 'route' => $route, 'profile'=> $profile, 'jsonProfile'=>$jsonProfile, 'currentRouteIndex'=>$currentRouteIndex, 'checkpointData'=>$checkpointData, 'checkpointDistances'=>$checkpointDistances, 'minTime'=>$getMinTime, 'getFinishedAthletes'=>$getFinishedAthletes, 'tail'=>$tail, 'event_type'=>$event_type));

    // automatically update data from server
    public function poll($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();

        // $colorArray = ["00FF00","0000FF","FF0000","FFFF00","00FFFF","FF00FF","00FF80","8000FF","FF8000","80FF00","0080FF","FF0080","80FF80","8080FF","FF8080","FFFF80","80FFFF","FF80FF","80FFBF","BF80FF","FFBF80","BFFF80","80BFFF","FF80BF"];
        $colorArray = ["00FF00","0000FF","FF0000","FFFF00","00FFFF","FF00FF","00FF80","8000FF","FF8000","80FF00","0080FF","FF0080","009900","000099","990000","999900","009999","990099","00994D","4D0099","994D00","4D9900","004D99","99004D"];

        // if not empty localstorage
        if ( !empty($_GET['bib_numbers']) ){
            $bib_numbers = json_decode($_GET['bib_numbers']);
            sort($bib_numbers);

            $data = [];
            $count = 0; // count index of $colorArray
            foreach ($bib_numbers as $key => $bib_number) {
                $deviceData = LiveTracking_Model::getLocationsViaBibNumber($event_id, $event->datetime_from, $event->datetime_to, $bib_number, $colorArray[$count]);
                $data[$bib_number] = $deviceData;
                $count++;
            }
            // echo '<pre>'.print_r($bib_numbers, 1).'</pre>';
        } else {
            // get 20 athletes from db
            if (Auth::check()){
                $athletes = DeviceMapping_Model::getAthletesProfile($event_id, true, true, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
            } else {
                $athletes = DeviceMapping_Model::getAthletesProfile($event_id, false, true, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
            }

            $data = [];
            $count = 0; // count index of $colorArray
            foreach ($athletes as $key => $athlete) {
                $deviceData = LiveTracking_Model::getLocationsViaBibNumber($event_id, $event->datetime_from, $event->datetime_to, $athlete->bib_number, $colorArray[$count]);
                $data[$athlete->bib_number] = $deviceData;
                $count++;
            }
        }
        // echo '<pre>'.print_r($data, 1).'</pre>';

        return response()->json($data);
    }


    public function checkpointTable($event_id){
        // if not empty localstorage
        if ( !empty($_GET['bib_numbers']) ){
            $bib_numbers = json_decode($_GET['bib_numbers']);
            sort($bib_numbers);

            $data = [];
            foreach ($bib_numbers as $key => $bib_number) {
                $reachedCheckpointData = LiveTracking_Model::getReachedCheckpointData($event_id, $bib_number);

                $data[$bib_number] = $reachedCheckpointData;
            }
            // echo '<pre>'.print_r($bib_numbers, 1).'</pre>';
        } else {
            // get 20 athletes from db
            if (Auth::check()){
                $athletes = DeviceMapping_Model::getAthletesProfile($event_id, true, true, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
            } else {
                $athletes = DeviceMapping_Model::getAthletesProfile($event_id, false, true, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
            }
            // print_r($athletes);
            $data = [];
            foreach ($athletes as $key => $athlete) {
                $reachedCheckpointData = LiveTracking_Model::getReachedCheckpointData($event_id, $athlete->bib_number);
                $data[$athlete->bib_number] = $reachedCheckpointData;
            }
        }
        usort($data, array($this, "cmp"));
        $tempCheckpoint = DB::table('gps_live_'.$event_id.'.checkpoint')->get();

        $dataArray = [];
        foreach ($data as $value) {
            $tempArray = [];
            $tempArray[] = $value['bib_number'].' '.$value['name'];

            foreach ($tempCheckpoint as $index => $checkpoint) {
                if( ($index != 0) && ($checkpoint->display == 1) ) {
                    if( !empty($value['data']) ) {
                        $tempDatetime = "";
                        foreach ($value['data'] as $item) {
                            if($item->checkpoint_id == $checkpoint->checkpoint_id){
                                $tempDatetime = $item->datetime;
                            }
                        }
                        $tempArray[] = $tempDatetime;
                    } else {
                        $tempArray[] = "";
                    }
                }
            }
            $dataArray[] = $tempArray;
        }
        return response()->json(array('data' => $dataArray));
    }


    public function cmp($a, $b){
        if ( !empty($a['data'][0]) && !empty($b['data'][0]) ){
            if( $a['data'][0]->checkpoint_id > $b['data'][0]->checkpoint_id) {
                return -1;
            } else if ($a['data'][0]->checkpoint_id == $b['data'][0]->checkpoint_id) {
                if(strtotime($a['data'][0]->datetime) < strtotime($b['data'][0]->datetime)) {
                    return -1;
                } else if ( strtotime($a['data'][0]->datetime) == strtotime($b['data'][0]->datetime) ) {
                    if ($a['bib_number'] < $b['bib_number']) {
                        return -1;
                    } else {
                        return 1;
                    }
                } else {
                    return 1;
                }
            } else {
                return 1;
            }
        } else {
            if ( !empty($a['data'][0]) && empty($b['data'][0]) ) {
                return -1;
            } else if ( empty($a['data'][0]) && !empty($b['data'][0]) ) {
                return 1;
            } else {
                if ($a['bib_number'] < $b['bib_number']) {
                    return -1;
                } else {
                    return 1;
                }
            }
        }
    }

}



    // // automatically update data from server
    // public function poll($event_id) {
    //
    //     // run calculation.php
    //     // shell_exec("php ".public_path()."/calculation.php 'alert' >> ".public_path()."/calculation.log");
    //     shell_exec("php ".public_path()."/calculation.php > /dev/null 2>/dev/null &");
    //
    //     $event = DB::table('gps_live.events')->where('event_id', $event_id)->first();
    //
    //     // data for drawing map
    //     if (Auth::check()) {
    //         $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to, true);
    //         foreach ($data as $value) {
    //             $progressData = LiveTracking_Model::getRouteDistanceByDevice($event_id, $value->device_id);
    //             $value->distance = !empty($progressData) ? $progressData[0]->distance : 0;
    //         }
    //         $jsonData = json_encode($data);
    //         $profile = DeviceMapping_Model::getAthletesProfile($event_id, true, false, true);
    //     }else{
    //         $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to, false);
    //         foreach ($data as $value) {
    //             $progressData = LiveTracking_Model::getRouteDistanceByDevice($event_id, $value->device_id);
    //             $value->distance = !empty($progressData) ? $progressData[0]->distance : 0;
    //         }
    //         $jsonData = json_encode($data);
    //         $profile = DeviceMapping_Model::getAthletesProfile($event_id, false, false, true);
    //     }
    //
    //     // get checkpoint times
    //     $getCheckpointData = (array) LiveTracking_Model::getCheckpointData($event_id);
    //     $checkpointData = $this->group_by($getCheckpointData, 'device_id');
    //
    //     // get athlete's distances for elevation chart
    //     $currentRouteIndex = LiveTracking_Model::getRouteDistance($event_id);
    //
    //     // Event Type & Tail
    //     $event_type = DB::table('events')
    //         ->where('event_id',$event_id)
    //         ->select('event_type')
    //         ->first();
    //     if ($event_type->event_type != 'fixed route'){
    //         // get data within 10 minutes for tail
    //         $tailTemp = (array) LiveTracking_Model::getTail($event_id);
    //         $tail = $this->group_by($tailTemp, 'device_id');
    //     } else {
    //         $tail = '[]';
    //     }
    //
    //     $array = [];
    //     $array['data'] = $data;
    //     $array['checkpointData'] = $checkpointData;
    //     $array['currentRouteIndex'] = $currentRouteIndex;
    //     $array['tail'] = $tail;
    //
    //     // echo "<pre>".print_r($array,1)."</pre>";
    //
    //     return response()->json($array);
    // }

    // group data
    // private function group_by($array, $key) {
    //     $return = array();
    //     foreach($array as $val) {
    //         $return[$val->$key][] = $val;
    //     }
    //     return $return;
    // }
