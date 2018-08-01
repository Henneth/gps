<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Support\Facades\Auth;
use App\CreateEvent_Model as CreateEvent_Model;
class EventController extends Controller {

    public function viewAllEvents() {
        $events = DB::table('events')->orderby('event_id', 'desc')->get();

        // without user login
        return view('view-all-events')->with(array('events' => $events));
    }


    public function createNewEvent() {
        return view('create-new-event');
    }


    public function createNewEventPost() {
        if (empty($_POST['event-name'])) {
            return redirect('create-new-event')->with('error', 'Event name must not be empty.');
        }

        if (empty($_POST['optionsRadios'])) {
            return redirect('create-new-event')->with('error', 'Event type must not be empty.');
        }
        $event_name = $_POST['event-name'];
        $event_type = $_POST['optionsRadios'];
        $start_name = $_POST['start-time'];
        $end_name = $_POST['end-time'];
        $d1 = new DateTime($start_name);
        $d2 = new DateTime($end_name);

        if ($d1 > $d2) {
            return redirect('create-new-event')->with('error', 'Start time must be earlier than end time.');
        }

        DB::table('events')->insert(
            ['event_name' => $event_name, 'event_type' => $event_type, 'datetime_from' => $start_name, 'datetime_to' => $end_name]
        );

        // get current event_id for creating new gps_live DB
        $event_id = DB::getPdo()->lastInsertId();
        CreateEvent_Model::createLiveDB($event_id);
        echo '<pre>'.print_r($event_id,1).'</pre>';

        // return redirect('create-new-event')->with('success', 'Event Created.');
    }


    public function portEventMapping(){
        $events = DB::table('events')->orderby('event_id', 'desc')->get();
        // echo '<pre>'.print_r($events,1).'</pre>';

        // read the file
        $mapping_file_path = storage_path('/')."ports_events_mapping.txt";
        $mapping_file = fopen($mapping_file_path, "r") or die("Unable to open file!");
        $mappingJson = fread($mapping_file,filesize($mapping_file_path));
        $mappingArray = (array) json_decode($mappingJson);
        // echo '<pre>'.print_r($mappingArray,1).'</pre>';
        fclose($mapping_file);

        return view('port-event-mapping')->with(array('events' => $events, 'mappingArray' => $mappingArray));
    }


    public function portEventMappingPost(){
        // create an empty array to store the port(key) and event_id (value)
        $mappingArray = [];
        if(!empty($_POST['40001'])){
            $mappingArray['40001'] = $_POST['40001'];
        }
        if(!empty($_POST['40002'])){
            $mappingArray['40002'] = $_POST['40002'];
        }
        if(!empty($_POST['40003'])){
            $mappingArray['40003'] = $_POST['40003'];
        }
        if(!empty($_POST['40004'])){
            $mappingArray['40004'] = $_POST['40004'];
        }
        if(!empty($_POST['40005'])){
            $mappingArray['40005'] = $_POST['40005'];
        }

        $mappingJson = json_encode($mappingArray);

        // W+: Open a file for read/write. Erases the contents of the file or creates a new file if it doesn't exist. File pointer starts at the beginning of the file
        $mapping_file = fopen(storage_path('/')."ports_events_mapping.txt", "w+") or die("Unable to open file!");
        fwrite($mapping_file, $mappingJson);
        fclose($mapping_file);

        return redirect('port-event-mapping')->with('success', 'Ports and Events have been mapped.');
    }


}
