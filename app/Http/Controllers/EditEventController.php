<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class EditEventController extends Controller {

    public function index($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();
        return view('edit-event')->with(array('event' => $event, 'event_id' => $event_id));
    }
    public function postEditEvent($event_id) {
        if (empty($_POST['optionsRadios'])) {
            return redirect('event/'.$event_id.'/edit-event')->with('error', 'Event type must not be empty.');
        }
        // clear route progress
        DB::table('route_progress')
            ->where('event_id', $event_id)
            ->delete();

        $datetime_from = $_POST['start-time'];
        $datetime_to = $_POST['end-time'];
        $event_type = $_POST['optionsRadios'];
        DB::table('events')
            ->where('event_id', $event_id)
            ->update(
                ['datetime_from' => $datetime_from, 'datetime_to' => $datetime_to, 'event_type' =>$event_type]
            );
        return redirect('event/'.$event_id.'/edit-event')->with('success', 'Event saved.');
    }

}
