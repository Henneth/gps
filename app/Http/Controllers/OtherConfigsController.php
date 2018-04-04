<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class OtherConfigsController extends Controller {

    public function index($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();
        return view('other-configurations')->with(array('event' => $event, 'event_id' => $event_id));
    }
    public function postOtherConfigs($event_id) {
        $datetime_from = $_POST['start-time'];
        $datetime_to = $_POST['end-time'];
        DB::table('events')
            ->where('event_id', $event_id)
            ->update(
                ['datetime_from' => $datetime_from, 'datetime_to' => $datetime_to]
            );
        return redirect('event/'.$event_id.'/other-configurations')->with('success', 'Configuration saved.');
    }

}
