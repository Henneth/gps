<?php

namespace App\Http\Controllers;

use DB;
use Request;
use App\Http\Controllers\Controller;
use App\DrawRoute_Model as DrawRoute_Model;
use DateTime;

class DrawRouteController extends Controller {

    public function index($event_id) {

        $event = DB::table('events')
        	->where('event_id', $event_id)
        	->select('event_type', 'live')
        	->first();

        $route = DB::table('gps_live_'.$event_id.'.map_point')
            ->select('latitude', 'longitude', 'is_checkpoint', 'display')
        	->get();
        $data = json_encode($route);

        $checkpoints = DB::table('gps_live_'.$event_id.'.checkpoint')
            ->select('checkpoint_no', 'checkpoint_name', 'min_time', 'latitude', 'longitude', 'display')
            ->get();

        // echo "<pre>".print_r($checkpoints,1)."</pre>";
        return view('draw-route')->with(array('event_id' => $event_id, 'data'=>$data, 'event'=>$event, 'checkpoints'=>$checkpoints));
    }

    public function saveRoute($event_id) {

		$route = $_POST['route'];
        $routeDecode = json_decode($route);
        // echo "<pre>".print_r($checkpoints,1)."</pre>";

        $index = 0;
        $totalDistance = 0;
        $distanceArray = [];
        $checkpoint_no = 1; // checkpoint number
        $checkpointArray = []; // for checkpoint table
        foreach ($routeDecode as $key => $value) {
            if ($index != 0){
                $lat1 = $routeDecode[$index-1]->lat;
                $lon1 = $routeDecode[$index-1]->lon;
                $lat2 = $routeDecode[$index]->lat;
                $lon2 = $routeDecode[$index]->lon;
                $currentDistance = round($this->distance($lat1, $lon1, $lat2, $lon2, "K") * 1000);
                $totalDistance += $currentDistance;

                $tempArray['latitude'] = $lat2;
                $tempArray['longitude'] = $lon2;
                $tempArray['distance_from_last_point'] = $currentDistance;
                $tempArray['distance_from_start'] = $totalDistance;
                $tempArray['display'] = $routeDecode[$index]->display;

                $tempArray ['is_checkpoint'] = property_exists($value, 'is_checkpoint') ? $value->is_checkpoint : 0;
                if (property_exists($value, 'is_checkpoint') && $value->is_checkpoint == 1) {
                    $tempArray['checkpoint_no'] = $checkpoint_no;
                    $checkpointRow = $tempArray;

                    $checkpointRow['point_order'] = $index + 1;
                    $checkpointArray[] = $checkpointRow;
                    $checkpoint_no++;
                } else {
                    $tempArray ['checkpoint_no'] = NULL;
                }
                // echo "<pre>".print_r($tempArray,1)."</pre>";

            } else {
                $tempArray['latitude'] = $routeDecode[$index]->lat;
                $tempArray['longitude'] = $routeDecode[$index]->lon;
                $tempArray['distance_from_last_point'] = 0;
                $tempArray['distance_from_start'] = 0;
                $tempArray['display'] = 1;
                $tempArray['is_checkpoint'] = property_exists($value, 'is_checkpoint') ? $value->is_checkpoint : 0;
                $tempArray['checkpoint_no'] = 0;
                $checkpointArray[] = $tempArray;
                // echo "<pre>".print_r($tempArray,1)."</pre>";
            }
            $tempArray ['checkpoint_name'] = NULL;
            $distanceArray [] = $tempArray;

            $index++;
        }

        $distanceArray[sizeof($distanceArray)-1]['checkpoint_name'] = 'Finish';

        $ckptRow = [];
        $ckptArray = [];
        foreach ($checkpointArray as $index => $checkpoint) {

            // echo sizeof($checkpointArray) - 1;
            if ($index != 0) {
                if($index == (count($checkpointArray)-1)){
                    $ckptRow['checkpoint_no'] = $checkpoint['checkpoint_no'];
                    $ckptRow['latitude'] = $checkpoint['latitude'];
                    $ckptRow['longitude'] = $checkpoint['longitude'];
                    $ckptRow['point_order'] = $checkpoint['point_order'];
                    $ckptRow['distance_from_start'] = $checkpoint['distance_from_start'];
                    $ckptRow['distance_to_next_ckpt'] = NULL;
                    $ckptRow['display'] = 1;
                    $ckptArray[] = $ckptRow;
                }else{
                    $ckptRow['checkpoint_no'] = $checkpoint['checkpoint_no'];
                    $ckptRow['latitude'] = $checkpoint['latitude'];
                    $ckptRow['longitude'] = $checkpoint['longitude'];
                    $ckptRow['point_order'] = $checkpoint['point_order'];
                    $ckptRow['distance_from_start'] = $checkpoint['distance_from_start'];
                    $ckptRow['distance_to_next_ckpt'] = $checkpointArray[$index+1]['distance_from_start'] - $checkpoint['distance_from_start'];
                    $ckptRow['display'] = $checkpoint['display'];
                    $ckptArray[] = $ckptRow;
                }
            } else {
                $ckptRow['checkpoint_no'] = $checkpoint['checkpoint_no'];
                $ckptRow['latitude'] = $checkpoint['latitude'];
                $ckptRow['longitude'] = $checkpoint['longitude'];
                $ckptRow['point_order'] = 1;
                $ckptRow['distance_from_start'] = $checkpoint['distance_from_start'];
                $ckptRow['distance_to_next_ckpt'] = $checkpointArray[$index+1]['distance_from_start'];
                $ckptRow['display'] = 1;
                $ckptArray[] = $ckptRow;
            }
        }
        // echo "<pre>".print_r($ckptArray,1)."</pre>";


        DB::transaction(function () use($event_id, $distanceArray, $ckptArray) {
            DB::table('gps_live_'.$event_id.'.map_point')->truncate();
            DB::table('gps_live_'.$event_id.'.map_point')
                ->insert($distanceArray);

            DB::table('gps_live_'.$event_id.'.checkpoint')->truncate();
            DB::table('gps_live_'.$event_id.'.checkpoint')
                ->insert($ckptArray);
        });

        return redirect('event/'.$event_id.'/draw-route')->with('success', 'Route updated.');


// // ----

        // $index = 0;
        // $totalDistance = 0;
        // $distanceArray = [];
        // $checkpoint = 1;
        // foreach ($routeDecode as $key => $value) {
        //     if ($index != 0){
        //         $lat1 = $routeDecode[$index-1]->lat;
        //         $lon1 = $routeDecode[$index-1]->lon;
        //         $lat2 = $routeDecode[$index]->lat;
        //         $lon2 = $routeDecode[$index]->lon;
        //         $currentDistance = round($this->distance($lat1, $lon1, $lat2, $lon2, "K") * 1000);
        //         $totalDistance += $currentDistance;
        //
        //         $tempArray ['route_index'] = $key;
        //         $tempArray ['distance'] = $totalDistance;
        //         $tempArray ['is_checkpoint'] = property_exists($value, 'isCheckpoint') ? $value->isCheckpoint : 0;
        //         if (property_exists($value, 'isCheckpoint') && $value->isCheckpoint == 1) {
        //             $tempArray ['checkpoint'] = $checkpoint;
        //             $checkpoint++;
        //         } else {
        //             $tempArray ['checkpoint'] = NULL;
        //         }
        //         // echo "<pre>".print_r($tempArray,1)."</pre>";
        //
        //     } else {
        //         $currentDistance = 0;
        //         $tempArray ['route_index'] = $key;
        //         $tempArray ['distance'] = $currentDistance;
        //         $tempArray ['is_checkpoint'] = property_exists($value, 'isCheckpoint') ? $value->isCheckpoint : 0;
        //         $tempArray ['checkpoint'] = 0;
        //
        //         // echo "<pre>".print_r($tempArray,1)."</pre>";
        //     }
        //     $tempArray ['checkpoint_name'] = NULL;
        //     $tempArray ['event_id'] = $event_id;
        //     $distanceArray [] = $tempArray;
        //
        //     $index++;
        //     // echo $key;
        //     // echo "<pre>".print_r($routeDecode[$key],1)."</pre>";
        //     // echo "<pre>".print_r($routeDecode[$key]->lon,1)."</pre>";
        // }
        //
        // $distanceArray[sizeof($distanceArray)-1]['checkpoint_name'] = 'Finish';
        // // echo "<pre>".print_r($distanceArray,1)."</pre>";
        // DB::table('route_distances')->where('event_id', $event_id)->delete();
		// DB::table('route_distances')
		// 	->insert($distanceArray);
        //
        // DrawRoute_Model::drawRouteUpdate($event_id, $route);
		// return redirect('event/'.$event_id.'/draw-route')->with('success', 'Route updated.');
    }

    public function saveMinimumTimes($event_id) {

        foreach ($_POST['min_times'] as $checkpoint_no => $min_time) {

            $min_time = !empty($min_time) ? $min_time : null;
            if (empty($min_time) || $this->isValidTime($min_time)) {

                DB::transaction(function () use ($event_id, $checkpoint_no, $min_time) {
                    DB::table('gps_live_'.$event_id.'.map_point')
                        ->where('checkpoint_no', $checkpoint_no)
                        ->update(
                            ['min_time' => $min_time]
                        );

                    DB::table('gps_live_'.$event_id.'.checkpoint')
                        ->where('checkpoint_no', $checkpoint_no)
                        ->update(
                            ['min_time' => $min_time]
                        );
                });

            } else {
                return redirect('event/'.$event_id.'/draw-route')->with('error', 'Minimum time is not in correct format.');
            }
        }
        return redirect('event/'.$event_id.'/draw-route')->with('success', 'Minimum times saved.');
    }

    private function isValidTime($time, $format = 'H:i:s') {
        $d = DateTime::createFromFormat($format, $time);
        return $d && $d->format($format) == $time;
    }

    public function saveCheckpointName($event_id) {
        // echo "<pre>".print_r($_POST,1)."</pre>";

        foreach ($_POST['checkpoint_name'] as $checkpoint_no => $checkpoint_name) {
            $checkpoint_name = !empty($checkpoint_name) ? $checkpoint_name : null;

            DB::transaction(function () use ($event_id, $checkpoint_no, $checkpoint_name) {
                DB::table('gps_live_'.$event_id.'.map_point')
                    ->where('checkpoint_no', $checkpoint_no)
                    ->update(
                        ['checkpoint_name' => $checkpoint_name]
                    );

                DB::table('gps_live_'.$event_id.'.checkpoint')
                    ->where('checkpoint_no', $checkpoint_no)
                    ->update(
                        ['checkpoint_name' => $checkpoint_name]
                    );
            });

        }
        return redirect('event/'.$event_id.'/draw-route')->with('success', 'Name of checkpoints are saved.');
    }

/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/*::                                                                         :*/
/*::  This routine calculates the distance between two points (given the     :*/
/*::  latitude/longitude of those points). It is being used to calculate     :*/
/*::  the distance between two locations using GeoDataSource(TM) Products    :*/
/*::                                                                         :*/
/*::  Definitions:                                                           :*/
/*::    South latitudes are negative, east longitudes are positive           :*/
/*::                                                                         :*/
/*::  Passed to function:                                                    :*/
/*::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  :*/
/*::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  :*/
/*::    unit = the unit you desire for results                               :*/
/*::           where: 'M' is statute miles (default)                         :*/
/*::                  'K' is kilometers                                      :*/
/*::                  'N' is nautical miles                                  :*/
/*::  Worldwide cities and other features databases with latitude longitude  :*/
/*::  are available at https://www.geodatasource.com                         :*/
/*::                                                                         :*/
/*::  For enquiries, please contact sales@geodatasource.com                  :*/
/*::                                                                         :*/
/*::  Official Web site: https://www.geodatasource.com                       :*/
/*::                                                                         :*/
/*::         GeoDataSource.com (C) All Rights Reserved 2017		   		     :*/
/*::                                                                         :*/
/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
//https://www.geodatasource.com/developers/php
    private function distance($lat1, $lon1, $lat2, $lon2, $unit) {
        if ($lat1 == $lat2 && $lon1 == $lon2){
            return 0;
        }

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}
