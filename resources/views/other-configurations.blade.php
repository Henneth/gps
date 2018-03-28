@extends('app')

@section('htmlheader_title')
    Other Configurations
@endsection

@section('contentheader_title')
    Other Configurations
@endsection

@section('main-content')
    @include('partials/alerts')
    <div class="container-flex">
        <div class="box box-primary">
            <form method="post" action="{{url('/')}}/event/{{$event->event_id}}/other-configurations/post">
                {{ csrf_field() }}
                <div class="box-header">
                    <h3 class="box-title">Event Configurations</h3>
                </div>
                <div class="box-body">
                        <div class="form-group">
                            <label>Event name:</label>
                            <input type="text" class="form-control" name="event_name" value="{{$event->event_name}}" disabled>
                        </div>
                        <!-- /.form group -->

                        <!-- Date and time range -->
                        <div class="form-group">
                            <label>Date and time range:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-clock-o"></i>
                                </div>
                                <input type="text" class="form-control pull-right" id="daterangepicker" name="datetime_range" autocomplete="off">
                            </div>
                            <!-- /.input group -->
                        </div>
                        <!-- /.form group -->

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
        $(function() {
            $('#daterangepicker').daterangepicker({
                timePicker: true,
                timePickerIncrement: 30,
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss',
                    separator: " to "
                }
            });
        });
    </script>
@endsection
