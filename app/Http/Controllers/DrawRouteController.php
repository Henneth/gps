<?php

namespace App\Http\Controllers;

use DB;
use Request;
use App\Http\Controllers\Controller;

class DrawRouteController extends Controller {

    public function index($event_id) {
        $data = DB::table('events')
        	->where('event_id',$event_id)
        	->select('route')
        	->first();

        $gpxData = DB::table('routes') 
            ->where('event_id', $event_id)
            ->select('latitude', 'longitude')
            ->get();
        $gpxData = json_encode($gpxData);  
            
        return view('draw-route')->with(array('event_id' => $event_id, 'data'=>$data, 'gpxData'=>$gpxData));
    }

    public function saveRoute($event_id) {
		$route = $_POST['route'];
		print_r($route);
		DB::table('events')
			->where('event_id', $event_id)
			->update(['route' => $route]);
		return redirect('event/'.$event_id.'/draw-route')->with('success', 'Route updated.');
    }
}
