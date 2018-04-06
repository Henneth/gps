<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class AthletesController extends Controller {

    public function index($event_id) {
        $athletes = DB::table('athletes')
            ->leftJoin('countries', 'athletes.country_code', '=', 'countries.code')
            ->orderby('athlete_id', 'desc')
            ->get();
        return view('athletes')->with(array('athletes' => $athletes, 'event_id' => $event_id));
    }
    public function addAthlete($event_id) {
        if (empty($_POST['bib_number']) || empty($_POST['first_name'])) {
            return redirect('event/'.$event_id.'/athletes')->with('error', 'Bib number and first name must not be empty.');
        }

        DB::table('athletes')->insert([
            'bib_number' => $_POST['bib_number'],
            'first_name' => $_POST['first_name'],
            'last_name' => !empty($_POST['last_name']) ? $_POST['last_name'] : NULL,
            'country_code' => !empty($_POST['country_code']) ? $_POST['country_code'] : NULL,
            'colour_code' => !empty($_POST['colour_code']) ? $_POST['colour_code'] : NULL,
        ]);
        return redirect('event/'.$event_id.'/athletes')->with('success', 'Athlete is added.');
    }
    public function editAthlete($event_id) {
        print_r($_POST);
        if (empty($_POST['athlete_id']) || empty($_POST['bib_number']) || empty($_POST['first_name'])) {
            // return redirect('event/'.$event_id.'/athletes')->with('error', 'Athlete ID, bib number and first name must not be empty.');
        }

        DB::table('athletes')
            ->where('athlete_id', $_POST['athlete_id'])
            ->update([
            'bib_number' => $_POST['bib_number'],
            'first_name' => $_POST['first_name'],
            'last_name' => !empty($_POST['last_name']) ? $_POST['last_name'] : NULL,
            'country_code' => !empty($_POST['country_code']) ? $_POST['country_code'] : NULL,
            'colour_code' => !empty($_POST['colour_code']) ? $_POST['colour_code'] : NULL,
        ]);
        return redirect('event/'.$event_id.'/athletes')->with('success', 'Athlete is edited.');
    }

}
