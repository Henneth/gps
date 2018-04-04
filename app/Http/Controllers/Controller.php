<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use DB;
use View;

abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

    protected $events;

    public function __construct()
    {
        // Fetch the Site Settings object
        $this->events = DB::table('events')->orderby('event_id', 'desc')->get();
        View::share('events', $this->events);
    }
}
