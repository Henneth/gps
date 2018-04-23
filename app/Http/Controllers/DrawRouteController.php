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
        // print_r($data);
        return view('draw-route')->with(array('event_id' => $event_id, 'data'=>$data));
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
