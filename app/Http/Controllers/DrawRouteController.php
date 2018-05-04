<?php

namespace App\Http\Controllers;

use DB;
use Request;
use App\Http\Controllers\Controller;
use App\DrawRoute_Model as DrawRoute_Model;


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

        // echo "<pre>".print_r($event_type,1)."</pre>";
        return view('draw-route')->with(array('event_id' => $event_id, 'data'=>$data, 'event_type'=>$event_type));
    }

    public function saveRoute($event_id) {
		$route = $_POST['route'];
		// print_r($route);
		// DB::table('routes')
		// 	->where('event_id', $event_id)
		// 	->update(['route' => $route]);
        DrawRoute_Model::drawRouteUpdate($event_id, $route);
		return redirect('event/'.$event_id.'/draw-route')->with('success', 'Route updated.');
    }
}
