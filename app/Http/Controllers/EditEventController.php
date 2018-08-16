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

        $datetime_from = $_POST['start-time'];
        $datetime_to = $_POST['end-time'];
        $event_type = $_POST['optionsRadios'];
        $hide_others = (isset($_POST['hide_others']) && $_POST['hide_others'] == 'on') ? 1 : 0;
        DB::table('events')
            ->where('event_id', $event_id)
            ->update(
                ['datetime_from' => $datetime_from, 'datetime_to' => $datetime_to, 'event_type' => $event_type, 'hide_others' => $hide_others]
            );

        if ($event_type == 'shortest route') {
			DB::table("gps_live_".$event_id.".checkpoint")->truncate();
			$maxPointOrder = DB::table("gps_live_".$event_id.".map_point")
                ->max('point_order');
			DB::table("gps_live_".$event_id.".map_point")
                ->where('is_checkpoint', 1)
                ->where('point_order', '!=', $maxPointOrder)
                ->update(['is_checkpoint' => 0, 'display' => 1, 'checkpoint_no' => null, 'checkpoint_name' => null, 'min_time' => null]);
        }

        return redirect('event/'.$event_id.'/edit-event')->with('success', 'Event saved.');
    }

    public function turnOnLive($event_id) {
        DB::table('events')
            ->where('event_id', $event_id)
            ->update(['live' => 1]);
        EditEvent_Model::processAthletes($event_id);
        return redirect('event/'.$event_id.'/edit-event')->with('success', 'Event is live.');
    }

    public function archive($event_id) {
        DB::table('events')
            ->where('event_id', $event_id)
            ->update(['live' => 2]);
        EditEvent_Model::copyToArchiveDB($event_id);
        return redirect('event/'.$event_id.'/edit-event')->with('success', 'Event is archived.');
    }

    public function revertToOriginal($event_id) {
        DB::table('events')
            ->where('event_id', $event_id)
            ->update(['live' => 0]);
        EditEvent_Model::revertToOriginal($event_id);
        return redirect('event/'.$event_id.'/edit-event')->with('success', 'Event is reverted to original.');
    }

}
