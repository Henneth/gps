<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <?php
            $live_events = []; // array only store live events, 'live is 1'
            $other_events = []; // 'live is 0 or 2'
            $current_event = [];
            foreach ($events as $value) {
                if($value->live == 1){
                    $live_events[] = $value;
                } else{
                    $other_events[] = $value;
                }


                if (!empty($event_id) && $event_id == $value->event_id) {
                    $current_event = $value;
                    $hideOthers = Auth::check() ? false : $current_event->hide_others;
                    $event_type = $current_event->event_type;
                    $event_name = $current_event->event_name;
                } else {
                    $hideOthers = false;
                }
            }

            $event_id = !empty($event_id) ? $event_id : 0;
            $event_type = !empty($event_type) ? $event_type : "";
            // echo '<pre>'.print_r($event_id,1).'</pre>';
        ?>


        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            @if(( $hideOthers && $current_event->live == 1 ) || ( !$hideOthers && !empty($live_events) ))
                <li class="header live-event">LIVE EVENT
                    <span class="pull-right-container">
                        <i class="fa fa-minus pull-right"></i>
                        <i class="fa fa-plus pull-right" style="display: none;"></i>
                    </span>
                    <div style="padding: 4px 0 0;color:#ccc;font-style: italic;font-size: 1.2em;">
                        @if( !empty($current_event) && $current_event->live == 1)
                            <span style="color: red;">●</span> {{$current_event->event_name}}
                        @endif
                        @if(!$hideOthers && !empty($live_events))
                            <div style="padding: 8px 0 4px;">
                                <select class="sidebar-select form-control" style="width: 100%;height: 28px;" tabindex="-1" aria-hidden="true">
                                    <option disabled selected>---- Select an event ----</option>
                                    @foreach ($live_events as $event)
                                        <option value="{{$event->event_id}}" {{ ($event->event_id == (!empty($event_id) ? $event_id : 0)) ? 'selected' : ''}}>{{$event->event_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </li>

                @if( !empty($current_event) )
                    @foreach ([['Live Tracking', 'fa-map-marker-alt']] as $item)
                        <li class="{{(Route::currentRouteName() == str_slug($item[0], '-') ) ? 'active' : ''}}"><a href="{{ url( 'event/' . $event_id . '/' . str_slug($item[0], '-') ) }}"><i class='fa {{$item[1]}}'></i> <span>{{$item[0]}}</span></a></li>
                    @endforeach
                    @if (Auth::check())
                        @foreach ([['Draw Route', 'fa-pencil-alt'], ['Athletes', 'icon-directions_run'], ['Device Mapping', 'fa-exchange-alt'], ['Edit Event', 'fas fa-cog']] as $item)
                            <li class="{{(Route::currentRouteName() == str_slug($item[0], '-') && $event_id == $event_id) ? 'active' : ''}}"><a href="{{ url( 'event/' . $event_id . '/' . str_slug($item[0], '-') ) }}"><i class='fa {{$item[1]}}'></i> <span>{{$item[0]}}</span></a></li>
                        @endforeach
                    @endif
                @endif
            @endif

            @if (!$hideOthers)
                <li class="header">ARCHIVE
                    <span class="pull-right-container">
                        <i class="fa fa-minus pull-right"></i>
                        <i class="fa fa-plus pull-right" style="display: none;"></i>
                    </span>
                    @if ($hideOthers)
                        <div style="padding: 4px 0 0;color:#ccc;font-style: italic;font-size: 1.2em;"><span style="color: #666;">●</span> {{$event_name}}</div>
                    @else
                        <div style="padding: 8px 0 4px;">
                            <select class="sidebar-select form-control" style="width: 100%;height: 28px;" tabindex="-1" aria-hidden="true">
                                <option disabled selected>---- Select an event ----</option>
                                @foreach ($other_events as $event)
                                    <option value="{{$event->event_id}}" {{ ($event->event_id == (!empty($event_id) ? $event_id : 0)) ? 'selected' : ''}}>{{$event->event_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </li>

                @if($event_id != 0)
                    @foreach ([['Replay Tracking', 'fa-redo']] as $item)
                        <li class="{{(Route::currentRouteName() == str_slug($item[0], '-') ) ? 'active' : ''}}"><a href="{{ url( 'event/' . $event_id . '/' . str_slug($item[0], '-') ) }}"><i class='fa {{$item[1]}}'></i> <span>{{$item[0]}}</span></a></li>
                    @endforeach

                    @if (Auth::check())
                        @if ($event_type == 'fixed route' || $event_type == 'shortest route')
                            @foreach ([['Draw Route', 'fa-pencil-alt'], ['Athletes', 'icon-directions_run'], ['Device Mapping', 'fa-exchange-alt'], ['Edit Event', 'fas fa-cog']] as $item)
                                <li class="{{(Route::currentRouteName() == str_slug($item[0], '-') ) ? 'active' : ''}}"><a href="{{ url( 'event/' . $event_id . '/' . str_slug($item[0], '-') ) }}"><i class='fa {{$item[1]}}'></i> <span>{{$item[0]}}</span></a></li>
                            @endforeach
                        @else
                            @foreach ([['Draw Route', 'fa-pencil-alt'], ['Checkpoint', 'fa-flag-checkered'], ['Athletes', 'icon-directions_run'], ['Device Mapping', 'fa-exchange-alt'], ['Edit Event', 'fas fa-cog']] as $item)
                                <li class="{{(Route::currentRouteName() == str_slug($item[0], '-') ) ? 'active' : ''}}"><a href="{{ url( 'event/' . $event_id . '/' . str_slug($item[0], '-') ) }}"><i class='fa {{$item[1]}}'></i> <span>{{$item[0]}}</span></a></li>
                            @endforeach
                        @endif

                        <script>var live_event_off = true;</script>
                    @endif
                @endif

            @endif

            @if (!$hideOthers)
                <li class="header">ALL EVENTS
                    <span class="pull-right-container">
                        <i class="fa fa-minus pull-right"></i>
                        <i class="fa fa-plus pull-right" style="display: none;"></i>
                    </span>
                </li>
                @foreach ([['View all events', 'fa-file-alt']] as $item)
                    <li class="{{Request::is( str_slug($item[0], '-') ) ? 'active' : ''}}"><a href="{{ url( str_slug($item[0], '-') ) }}"><i class='fa {{$item[1]}}'></i> <span>{{$item[0]}}</span></a></li>
                @endforeach

                @if (Auth::check())
                    @foreach ([['Create new event', 'fa-plus'], ['Raw Data', 'fa-database']] as $item)
                        <li class="{{Request::is( str_slug($item[0], '-') ) ? 'active' : ''}}"><a href="{{ url( str_slug($item[0], '-') ) }}"><i class='fa {{$item[1]}}'></i> <span>{{$item[0]}}</span></a></li>
                    @endforeach
                @endif
            @endif

            {{-- <li class="treeview">
            {{-- <li class="treeview">
                <a href="#"><i class='fa fa-link'></i> <span>Multilevel</span> <i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li><a href="#">Link in level 2</a></li>
                    <li><a href="#">Link in level 2</a></li>
                </ul>
            </li> --}}
        </ul><!-- /.sidebar-menu -->

    </section>
    <!-- /.sidebar -->
</aside>
