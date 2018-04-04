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
                                <i class="far fa-clock-o"></i>
                            </div>
                            <input type="text" class="form-control pull-right" name="start-time" id="start-time" autocomplete="off" placeholder="yyyy-mm-dd hh:mm">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="end-time">End Time</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="far fa-clock-o"></i>
                            </div>
                            <input type="text" class="form-control pull-right" name="end-time" id="end-time" autocomplete="off" placeholder="yyyy-mm-dd hh:mm">
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
