<?php

namespace App\Http\Controllers;

use DB;
use Request;
use App\Http\Controllers\Controller;
use App\Tickets;

class CheckpointController extends Controller {

    public function index($event_id) {
        $data = DB::table('routes')
            ->where('event_id',$event_id)
            ->select('route')
            ->first();

        $checkpoints = DB::table('checkpoint')
            ->select('checkpoint_id', 'latitude', 'longitude')
            ->where('event_id', $event_id)
            ->get();

        $allCheckpoints = json_encode($checkpoints);

        return view('checkpoint')->with(array('event_id' => $event_id,
            'data' => $data, 'allCheckpoints' => $allCheckpoints));
    }

    public function saveCheckpoint($event_id){
        $checkpoint = $_POST['checkpoint'];
        // print_r($checkpoint);
        $data = json_decode($checkpoint, true);
        print_r($data);

        DB::table('checkpoint')->where('event_id', '=', $event_id)->delete();
        // Model::insert($data);
        foreach ($data as &$value) {
            $value['event_id'] = $event_id;
        }
        // print_r($data);
        DB::table('checkpoint')->insert($data);
        return redirect('event/'.$event_id.'/checkpoint')->with('success', 'Checkpoint saved!');

    }

}
