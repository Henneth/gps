<?php

namespace App\Http\Controllers;

use DB;
use Request;
use App\Http\Controllers\Controller;
use App\Tickets;

class CheckPointController extends Controller {

    public function index($event_id) {
        $data = DB::table('events')
            ->where('event_id',$event_id)
            ->select('route')
            ->first();
        print_r($data);
        return view('checkpoint')->with(array('event_id' => $event_id, 'data'=>$data));
    }

    public function saveCheckpoint($event_id){
        $checkpoint = $_POST['checkpoint'];
        // print_r($checkpoint);
        $data = json_decode($checkpoint, true);
        print_r($data);
        // Model::insert($data);
        DB::table('checkpoint')->insert($data);

        // return redirect('event/'.$event_id.'/checkpoint')->with('status', 'Checkpoint saved!');

    }

}
