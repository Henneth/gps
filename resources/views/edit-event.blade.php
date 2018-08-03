@extends('app')

@section('htmlheader_title')
    Edit Event
@endsection

@section('contentheader_title')
    Edit Event
@endsection

@section('contentheader_class')
    display-inline-block
@endsection

{{-- @section('contentheader_right')
<div class="pull-right"><button class="btn btn-primary" onclick="toggleExcelImport();return false;"><i class="fas fa-upload"></i>&nbsp; Import GPX File</button></div>
@endsection --}}

@section('main-content')
    @include('partials/alerts')
    <div class="container-flex">
        {{-- <div id="excelImportBox" class="box box-primary" style="display: none;">
            <div class="box-header with-border">
                <h3 class="box-title">Import GPX File</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
                </div>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form role="form" action="{{url('/')}}/event/{{$event_id}}/edit-event/gpx-file-upload" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="box-body">
                    <div class="form-group">
                        <label for="excelFile">GPX file upload</label>
                        <input type="file" id="excelFile" name="fileToUpload">

                        <p class="help-block">.gpx file only.</p>
                    </div>
                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div> --}}
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
                            <input type="text" class="form-control pull-right" name="start-time" value="{{$event->datetime_from}}" id="start-time" autocomplete="off" {{!empty($event->live) ? 'disabled' : ''}} placeholder="yyyy-mm-dd hh:mm">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="end-time">End Time</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="far fa-clock"></i>
                            </div>
                            <input type="text" class="form-control pull-right" name="end-time" value="{{$event->datetime_to}}" id="end-time" autocomplete="off" {{!empty($event->live) ? 'disabled' : ''}} placeholder="yyyy-mm-dd hh:mm">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="end-time">Event Type:</label>
                        <div class="radio" style="margin-bottom: 16px;">
                            <label>
                                <input type="radio" name="optionsRadios" id="optionsRadios1" value="fixed route" {{$event->event_type == "fixed route" ? "checked" : ""}} {{!empty($event->live) ? 'disabled' : ''}}>
                                Fixed route
                            </label>
                            <div style="color: #999;">Several checkpoints in sequence with interim locations and actual route displayed.</div>
                        </div>
                        <div class="radio" style="margin-bottom: 16px;">
                            <label>
                                <input type="radio" name="optionsRadios" id="optionsRadios2" value="shortest route" {{$event->event_type == "shortest route" ? "checked" : ""}} {{!empty($event->live) ? 'disabled' : ''}}>
                                Shortest route
                            </label>
                            <div style="color: #999;">Several checkpoints in sequence without interim locations, map displayed with straight lines between checkpoints, and no elevation chart but tracks the last 10 minutes for each device.</div>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="optionsRadios" id="optionsRadios3" value="no route" {{$event->event_type == "no route" ? "checked" : ""}} {{!empty($event->live) ? 'disabled' : ''}}>
                                No route
                            </label>
                            <div style="color: #999;">Several checkpoints not in sequence, no map displayed, and no elevation chart but tracks the last 10 minutes for each device.</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="hide_others">Hide Other Events:</label>
                        <div class="checkbox">
                            <label><input type="checkbox" name="hide_others" {{$event->hide_others === 1 ? 'checked':''}} {{!empty($event->live) ? 'disabled' : ''}}>Hide other events</label>
                            <div style="color: #999;">Other events will not be shown on pages of this event, but admin wll not be affected by this option.</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="set-event-live">Event Status:</label>
                        @if (empty($event->live))
                            <div style="margin-bottom: 4px;">
                                <span style="color: #666;">● NOT LIVE</span>
                            </div>
                            <div>
                                <a href="{{url('/')}}/event/{{$event->event_id}}/edit-event/turn-on-live">
                                    <button type="button" class="btn btn-default" style="text-transform: capitalize;">Turn on live</button>
                                </a>
                            </div>
                        @endif
                        @if (!empty($event->live) && $event->live == 1)
                            <div style="margin-bottom: 4px;">
                                <span style="color: red;">● LIVE</span>
                            </div>
                            {{-- <label><input type="checkbox" name="event-live" {{$event->live === 1 ? 'checked':''}}>Set as live event</label> --}}
                            <div style="display: inline-block;">
                                <a href="{{url('/')}}/event/{{$event->event_id}}/edit-event/archive">
                                    <button type="button" class="btn btn-default" style="text-transform: capitalize;">Archive this event</button>
                                </a>
                            </div>
                            <div style="display: inline-block;">
                                <a href="{{url('/')}}/event/{{$event->event_id}}/edit-event/revert-to-original">
                                    <button type="button" class="btn btn-default" style="text-transform: capitalize;">Turn off live & clear processed data</button>
                                </a>
                            </div>
                        @endif
                        @if (!empty($event->live) && $event->live == 2)
                            <div style="margin-bottom: 4px;">
                                <span style="color: #666;">● ARCHIVED</span>
                            </div>
                            <div style="color: #999;">
                                This event is finished and archived.
                            </div>
                        @endif
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary" {{!empty($event->live) ? 'disabled' : ''}}>Save</button>
                </div>
            </form>
        </div>
    </div>
    <div id="map"></div>
</div>
@endsection

@section('js')
    <script>
        // function toggleExcelImport() {
        //     $('#excelImportBox').toggle();
        // }
        $('#start-time').datetimepicker({format: 'yyyy-mm-dd hh:ii'});
        $('#end-time').datetimepicker({format: 'yyyy-mm-dd hh:ii'});
    </script>
@endsection
