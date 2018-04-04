<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class DrawRouteController extends Controller {

    public function index($event_id) {
        // $data = DB::table('gps_data')->orderby('id', 'desc')->get();
        return view('draw-route')->with(array('event_id' => $event_id));
    }

}
