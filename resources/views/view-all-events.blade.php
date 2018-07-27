@extends('app')

@section('htmlheader_title')
    View All Events
@endsection

@section('contentheader_title')
    View All Events
@endsection

@section('main-content')
    @include('partials/alerts')
    <div class="container-flex">
        <div class="box">
            <div class="box-body">
                <ul class="nav nav-tabs">
                    <li id="view-events" class="active"><a href="#">Events</a></li>
                    @if (Auth::check())
                        <li id="event-mapping"><a href="#">Port/Event Mapping</a></li>
                    @endif
                </ul>
                <table id="view-all-events" class="table table-bordered">
                    <tbody>
                        <tr>
                            <th style="width: 10%">Event ID</th>
                            <th>Event Name</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            {{-- <th style="width: 40px">Label</th> --}}
                        </tr>
                        @foreach ($events as $event)
                            <tr>
                                <td>{{$event->event_id}}<span style="color:red;text-transform: uppercase;font-style: italic;font-weight: bold;">{{$event->current === 1 ? '&nbsp;&nbsp;●&nbsp;Live' : ''}}</span></td>
                                <td><a href="{{url('/')}}/event/{{$event->event_id}}">{{$event->event_name}}</a></td>
                                <td>{{$event->datetime_from}}</td>
                                <td>{{$event->datetime_to}}</td>
                                {{-- <td><span class="badge bg-red">55%</span></td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if (Auth::check())
                    <form id="mapping" method="post" action="{{url('/')}}/view-all-events/post">
                        {{ csrf_field() }}
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>Ports</th>
                                    <th>Events</th>
                                </tr>
                                <tr>
                                    <td><span style="color:#3b8dbc;text-transform: uppercase;font-style: italic;font-weight: bold;">40000</span></td>
                                    <td>
                                        <select class="form-control" disabled>
                                            <option value="">Achive</option>
                                        </select>
                                    </td>
                                </tr>

                                <?php
                                    $portsArray = ['40001', '40002', '40003', '40004', '40005'];
                                ?>
                                @for ($i=0; $i < sizeof($portsArray); $i++)
                                    <tr>
                                        <td><span style="color:#3b8dbc;text-transform: uppercase;font-style: italic;font-weight: bold;">{{$portsArray[$i]}}</span></td>
                                        <td>
                                          <select class="form-control" name="{{$portsArray[$i]}}">
                                            <option value="">Achive (default)</option>
                                            @foreach ($events as $event)
                                                <option value="{{$event->event_id}}" {{(array_key_exists($portsArray[$i], $mappingArray) ? $mappingArray[$portsArray[$i]] : null)== $event->event_id ? 'selected' : ''}}>{{$event->event_name}}</option>
                                            @endforeach
                                          </select>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </from>
                @endif
            </div>
            <!-- /.box-body -->
            {{-- <div class="box-footer clearfix">
                <ul class="pagination pagination-sm no-margin pull-right">
                    <li><a href="#">«</a></li>
                    <li><a href="#">1</a></li>
                    <li><a href="#">2</a></li>
                    <li><a href="#">3</a></li>
                    <li><a href="#">»</a></li>
                </ul>
            </div> --}}
        </div>
    </div>
@endsection
@section('js')
    <script>
        $('#mapping').hide();
        $('#event-mapping').click(function(){
            $('#view-all-events').hide();
            $('#mapping').show();
            $('#view-events').removeClass('active');
            $(this).addClass('active');
        })

        $('#view-events').click(function(){
            $('#mapping').hide();
            $('#view-all-events').show();
            $('#event-mapping').removeClass('active');
            $(this).addClass('active');
        })
    </script>
@endsection
