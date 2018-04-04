@extends('app')

@section('htmlheader_title')
    View All Events
@endsection

@section('contentheader_title')
    View All Events
@endsection

@section('main-content')
    <div class="container-flex">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Events</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <table class="table table-bordered">
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
                                <td>{{$event->event_id}}</td>
                                <td><a href="{{url('/')}}/event/{{$event->event_id}}">{{$event->event_name}}</a></td>
                                <td>{{$event->datetime_from}}</td>
                                <td>{{$event->datetime_to}}</td>
                                {{-- <td><span class="badge bg-red">55%</span></td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
