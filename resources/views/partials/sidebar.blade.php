<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        {{-- <div class="user-panel">
            <div class="pull-left image">
                <img src="{{asset('/img/user2-160x160.jpg')}}" class="img-circle" alt="User Image" />
            </div>
            <div class="pull-left info">
                <p>{{ Auth::user()->name }}</p>
                <!-- Status -->
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div> --}}

        {{-- <!-- search form (Optional) -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
              </span>
            </div>
        </form>
        <!-- /.search form --> --}}


        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            @if(current_event)
                <li class="header live-event">LIVE EVENT
                <span class="pull-right-container">
                    <i class="fa fa-minus pull-right"></i>
                    <i class="fa fa-plus pull-right" style="display: none;"></i>
                </span>
                @foreach ($events as $event)
                    @if(current_event == $event->event_id )
                        <div style="padding: 4px 0 0;color:#ccc;font-style: italic;font-size: 1.2em;"><span style="color: red;">●</span> {{$event->event_name}}</div>
                    @endif
                @endforeach
                </li>
                @foreach ([['Live Tracking', 'fa-map-marker-alt']] as $item)
                    <li class="{{(Route::currentRouteName() == str_slug($item[0], '-') ) ? 'active' : ''}}"><a href="{{ url( 'event/' . current_event . '/' . str_slug($item[0], '-') ) }}"><i class='fa {{$item[1]}}'></i> <span>{{$item[0]}}</span></a></li>
                @endforeach
                @if (Auth::check())
                    @foreach ([['Draw Route', 'fa-pencil-alt'], ['Athletes', 'icon-directions_run'], ['Device Mapping', 'fa-exchange-alt'], ['Edit Event', 'fas fa-cog']] as $item)
                        <li class="{{(Route::currentRouteName() == str_slug($item[0], '-') && $event_id == current_event) ? 'active' : ''}}"><a href="{{ url( 'event/' . current_event . '/' . str_slug($item[0], '-') ) }}"><i class='fa {{$item[1]}}'></i> <span>{{$item[0]}}</span></a></li>
                    @endforeach
                @endif
            @endif

{{--             @if (!Request::is('view-all-events') && !Request::is('create-new-event') && !Request::is('home') && !Request::is('raw-data')) --}}
            @if (empty($event_id))
                <?php $event_id = 0;?>
            @endif

            <li class="header">ARCHIVE
                <span class="pull-right-container">
                    <i class="fa fa-minus pull-right"></i>
                    <i class="fa fa-plus pull-right" style="display: none;"></i>
                </span>
                <div style="padding: 8px 0 4px;">
                    {{-- <label style="color: white;">Current Event</label> --}}
                    <select class="sidebar-select form-control" style="width: 100%;height: 28px;" tabindex="-1" aria-hidden="true">
                        <option disabled selected>---- Select an event ----</option>
                        @foreach ($events as $event)
                            @if(current_event != $event->event_id)
                                <option value="{{$event->event_id}}" {{ ($event->event_id == (!empty($event_id) ? $event_id : 0)) ? 'selected' : ''}}>{{$event->event_name}}</option>
                            @endif
                            <?php
                            if (!empty($event_id)) {
                                if ($event->event_id == $event_id) {
                                    $event_type = $event->event_type;
                                }
                            } else {
                                $event_type = "";
                            }
                            ?>
                        @endforeach
                    </select>
                </div>
            </li>

            @if(current_event != $event_id && $event_id != 0)
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

                    <script>
                        var live_event_off = true;
                    </script>
                @endif
            @endif

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
