@extends('app')

@section('htmlheader_title')
    Edit Event
@endsection

@section('contentheader_title')
    Edit Event
@endsection

@section('main-content')
    @include('partials/alerts')
    <div class="container-flex">
        <div class="box box-primary">
            <form method="post" action="{{url('/')}}/event/{{$event->event_id}}/edit-event/post">
                {{ csrf_field() }}
                <div class="box-body">
                    <div class="form-group">
                        <label>Event name:</label>
                        <input type="text" class="form-control" name="event_name" value="{{$event->event_name}}" disabled>
                    </div>
                    <div class="form-group">
                        <label for="start-time">Start Time</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="far fa-clock"></i>
                            </div>
                            <input type="text" class="form-control pull-right" name="start-time" value="{{$event->datetime_from}}" id="start-time" autocomplete="off" placeholder="yyyy-mm-dd hh:mm">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="end-time">End Time</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="far fa-clock"></i>
                            </div>
                            <input type="text" class="form-control pull-right" name="end-time" value="{{$event->datetime_to}}" id="end-time" autocomplete="off" placeholder="yyyy-mm-dd hh:mm">
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
    <div id="map"></div>
</div>
@endsection

@section('js')
    <script>
        $('#start-time').datetimepicker({format: 'yyyy-mm-dd hh:ii'});
        $('#end-time').datetimepicker({format: 'yyyy-mm-dd hh:ii'});
    </script>
@endsection
