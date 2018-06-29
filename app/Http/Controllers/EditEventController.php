<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\EditEvent_Model as EditEvent_Model;

class EditEventController extends Controller {

    public function index($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();

        return view('edit-event')->with(array('event' => $event, 'event_id' => $event_id));
    }
    public function postEditEvent($event_id) {
        if (empty($_POST['optionsRadios'])) {
            return redirect('event/'.$event_id.'/edit-event')->with('error', 'Event type must not be empty.');
        }
        // print_r($_POST);
        if (isset($_POST['event-live']) && $_POST['event-live'] == 'on') {
            // echo 'h';
            // check does any event live now
            $liveCheck = DB::table('events')
                ->select('current')
                ->where('current', 1)
                ->first();
            if (!empty($liveCheck)){
                return redirect('event/'.$event_id.'/edit-event')->with('error', 'There is a live event already.');
            }
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

        // clear current column value to zero first if event-live is ticked(1)
        if (isset($_POST['event-live']) && $_POST['event-live'] == 'on') {
            DB::table('events')
                ->where('event_id', $event_id)
                ->update(['current' => 1]);
            EditEvent_Model::copyToLiveDB($event_id);
        } else {
            // DB::table('events')
            //     ->where('event_id', $event_id)
            //     ->update(['current' => 0]);
            // EditEvent_Model::copyToArchiveDB($event_id);

        }
        return redirect('event/'.$event_id.'/edit-event')->with('success', 'Event saved.');
    }

    public function unsetLive($event_id) {
        if (!isset($_POST['event-live']) || $_POST['event-live'] == '') {
            DB::table('events')
                ->where('event_id', $event_id)
                ->update(['current' => 0]);
            EditEvent_Model::copyToArchiveDB($event_id);
            return redirect('event/'.$event_id.'/edit-event')->with('success', 'Event is archived.');
        }
        return redirect('event/'.$event_id.'/edit-event');
    }

}
