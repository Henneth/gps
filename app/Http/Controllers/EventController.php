<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use DateTime;

class EventController extends Controller {

    public function viewAllEvents() {
        $events = DB::table('events')->orderby('event_id', 'desc')->get();
        return view('view-all-events')->with(array('events' => $events));
    }
    public function createNewEvent() {
        return view('create-new-event');
    }
    public function createNewEventPost() {
        if (empty($_POST['event-name'])) {
            return redirect('create-new-event')->with('error', 'Event name must not be empty.');
        }

        $event_name = $_POST['event-name'];
        $start_name = $_POST['start-time'];
        $end_name = $_POST['end-time'];
        $d1 = new DateTime($start_name);
        $d2 = new DateTime($end_name);

        if ($d1 > $d2) {
            return redirect('create-new-event')->with('error', 'Start time must be earlier than end time.');
        }

        DB::table('events')->insert(
            ['event_name' => $event_name, 'datetime_from' => $start_name, 'datetime_to' => $end_name]
        );
        return redirect('create-new-event')->with('success', 'Event Created.');
    }

}
