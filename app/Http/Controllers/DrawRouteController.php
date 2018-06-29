<?php

namespace App\Http\Controllers;

use DB;
use Request;
use App\Http\Controllers\Controller;
use App\DrawRoute_Model as DrawRoute_Model;
use DateTime;

class DrawRouteController extends Controller {

    public function index($event_id) {
        $data = DB::table('routes')
        	->where('event_id',$event_id)
        	->select('route')
        	->first();
        $event_type = DB::table('events')
        	->where('event_id',$event_id)
        	->select('event_type')
        	->first();
        $checkpointMinTimes = DB::table('route_distances')
        	->where('event_id',$event_id)
        	->where('is_checkpoint',1)
            ->get();

        // echo "<pre>".print_r($event_type,1)."</pre>";
        return view('draw-route')->with(array('event_id' => $event_id, 'data'=>$data, 'event_type'=>$event_type, 'checkpointMinTimes'=>$checkpointMinTimes));
    }

    public function saveRoute($event_id) {

        // clear route progress
        DB::table('route_progress')
            ->where('event_id', $event_id)
            ->delete();

		$route = $_POST['route'];

        $routeDecode = json_decode($route);
        $index = 0;
        $totalDistance = 0;
        $distanceArray = [];
        $checkpoint = 1;
        foreach ($routeDecode as $key => $value) {
            if ($index != 0){
                $lat1 = $routeDecode[$index-1]->lat;
                $lon1 = $routeDecode[$index-1]->lon;
                $lat2 = $routeDecode[$index]->lat;
                $lon2 = $routeDecode[$index]->lon;
                $currentDistance = round($this->distance($lat1, $lon1, $lat2, $lon2, "K") * 1000);
                $totalDistance += $currentDistance;

                $tempArray ['route_index'] = $key;
                $tempArray ['distance'] = $totalDistance;
                $tempArray ['is_checkpoint'] = property_exists($value, 'isCheckpoint') ? $value->isCheckpoint : 0;
                if (property_exists($value, 'isCheckpoint') && $value->isCheckpoint == 1) {
                    $tempArray ['checkpoint'] = $checkpoint;
                    $checkpoint++;
                } else {
                    $tempArray ['checkpoint'] = NULL;
                }
                // echo "<pre>".print_r($tempArray,1)."</pre>";

            } else {
                $currentDistance = 0;
                $tempArray ['route_index'] = $key;
                $tempArray ['distance'] = $currentDistance;
                $tempArray ['is_checkpoint'] = property_exists($value, 'isCheckpoint') ? $value->isCheckpoint : 0;
                $tempArray ['checkpoint'] = 0;

                // echo "<pre>".print_r($tempArray,1)."</pre>";
            }
            $tempArray ['checkpoint_name'] = NULL;
            $tempArray ['event_id'] = $event_id;
            $distanceArray [] = $tempArray;

            $index++;
            // echo $key;
            // echo "<pre>".print_r($routeDecode[$key],1)."</pre>";
            // echo "<pre>".print_r($routeDecode[$key]->lon,1)."</pre>";
        }

        $distanceArray[sizeof($distanceArray)-1]['checkpoint_name'] = 'Finish';
        // echo "<pre>".print_r($distanceArray,1)."</pre>";
        DB::table('route_distances')->where('event_id', $event_id)->delete();
		DB::table('route_distances')
			->insert($distanceArray);

        DrawRoute_Model::drawRouteUpdate($event_id, $route);
		return redirect('event/'.$event_id.'/draw-route')->with('success', 'Route updated.');
    }

    public function saveMinimumTimes($event_id) {

        // clear route progress
        DB::table('route_progress')
            ->where('event_id', $event_id)
            ->delete();

        foreach ($_POST['min_times'] as $route_distance_id => $min_time) {
            $min_time = !empty($min_time) ? $min_time : null;
            if (empty($min_time) || $this->isValidTime($min_time)) {
                DB::table('route_distances')
                ->where('route_distance_id', $route_distance_id)
                ->update(
                    ['min_time' => $min_time]
                );
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
        foreach ($_POST['checkpoint_name'] as $route_distance_id => $checkpoint_name) {
            $checkpoint_name = !empty($checkpoint_name) ? $checkpoint_name : null;
            DB::table('route_distances')
            ->where('route_distance_id', $route_distance_id)
            ->update(
                ['checkpoint_name' => $checkpoint_name]
            );
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
