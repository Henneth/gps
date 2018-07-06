@extends('app')

@section('htmlheader_title')
    Create New Event
@endsection

@section('contentheader_title')
    Create New Event
@endsection

@section('main-content')
    @include('partials.alerts')
    <div class="container-flex">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Event Details</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form role="form" method="post" action="{{url('/')}}/create-new-event/post">
                {{ csrf_field() }}
                <div class="box-body">
                    <div class="form-group">
                        <label for="event-name">Event Name</label>
                        <input type="text" class="form-control" name="event-name" id="event-name" placeholder="Event Name">
                    </div>
                    <div class="form-group">
                        <label for="start-time">Start Time</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="far fa-clock"></i>
                            </div>
                            <input type="text" class="form-control pull-right" name="start-time" id="start-time" autocomplete="off" placeholder="yyyy-mm-dd hh:mm">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="end-time">End Time</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="far fa-clock"></i>
                            </div>
                            <input type="text" class="form-control pull-right" name="end-time" id="end-time" autocomplete="off" placeholder="yyyy-mm-dd hh:mm">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="end-time">Event Type:</label>
                        <div class="radio" style="margin-bottom: 16px;">
                            <label>
                                <input type="radio" name="optionsRadios" id="optionsRadios1" value="fixed route">
                                Fixed route
                            </label>
                            <div style="color: #999;">Several checkpoints in sequence with interim locations and actual route displayed.</div>
                        </div>
                        <div class="radio" style="margin-bottom: 16px;">
                            <label>
                                <input type="radio" name="optionsRadios" id="optionsRadios2" value="shortest route">
                                Shortest route
                            </label>
                            <div style="color: #999;">Several checkpoints in sequence without interim locations, map displayed with straight lines between checkpoints, and no elevation chart but tracks the last 10 positions for each device.</div>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="optionsRadios" id="optionsRadios3" value="no route">
                                No route
                            </label>
                            <div style="color: #999;">Several checkpoints not in sequence, no map displayed, and no elevation chart but tracks the last 10 positions for each device.</div>
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $('#start-time').datetimepicker({format: 'yyyy-mm-dd hh:ii'});
        $('#end-time').datetimepicker({format: 'yyyy-mm-dd hh:ii'});
    </script>
@endsection
