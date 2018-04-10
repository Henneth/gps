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

        <div style="padding: 12px 18px;">
            <label style="color: white;">Current Event</label>
            <select class="sidebar-select form-control" style="width: 100%;" tabindex="-1" aria-hidden="true">
                <option disabled selected>---- Select an event ----</option>
                @foreach ($events as $event)
                    <option value="{{$event->event_id}}" {{ ($event->event_id == (!empty($event_id) ? $event_id : 0)) ? 'selected' : ''}}>{{$event->event_name}}</option>
                @endforeach
            </select>
        </div>

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            @if (!Request::is('view-all-events') && !Request::is('create-new-event') && !Request::is('home') && !Request::is('raw-data'))

                {{-- <li class="header">CURRENT EVENT</li> --}}
                @foreach ([['Live Tracking', 'fa-map-marker-alt'], ['Replay Tracking', 'fa-redo']] as $item)
                    <li class="{{(Route::currentRouteName() == str_slug($item[0], '-') ) ? 'active' : ''}}"><a href="{{ url( 'event/' . $event_id . '/' . str_slug($item[0], '-') ) }}"><i class='fa {{$item[1]}}'></i> <span>{{$item[0]}}</span></a></li>
                @endforeach

                @if (Auth::check())
                    @foreach ([['Draw Route', 'fa-pencil-alt'], ['Athletes', 'icon-directions_run'], ['Device Mapping', 'fa-exchange-alt'], ['Other Configurations', 'fa-gear']] as $item)
                        <li class="{{(Route::currentRouteName() == str_slug($item[0], '-') ) ? 'active' : ''}}"><a href="{{ url( 'event/' . $event_id . '/' . str_slug($item[0], '-') ) }}"><i class='fa {{$item[1]}}'></i> <span>{{$item[0]}}</span></a></li>
                    @endforeach
                @endif

            @endif

            <li class="header">ALL EVENTS</li>
                @foreach ([['View all events', 'fa-file-text']] as $item)
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
