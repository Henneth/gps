<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class OtherConfigsController extends Controller {

    public function index($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();
        return view('other-configurations')->with(array('event' => $event));
    }
    public function postOtherConfigs($event_id) {
        $datetime_range = explode(' to ', $_POST['datetime_range']);
        $datetime_from = $datetime_range[0];
        $datetime_to = $datetime_range[1];
        DB::table('events')
            ->where('event_id', $event_id)
            ->update(
                ['datetime_from' => $datetime_from, 'datetime_to' => $datetime_to]
            );
        return redirect('event/'.$event_id.'/other-configurations')->with('success', 'Configuration saved.');
    }

}
