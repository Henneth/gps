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
                    <div class="form-group">
                        <label for="end-time">Event Type:</label>
                        <div class="radio">
                            <label>
                                <input type="radio" name="optionsRadios" id="optionsRadios1" value="fixed route">
                                Fixed route
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="optionsRadios" id="optionsRadios2" value="shortest route">
                                Shortest route
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="optionsRadios" id="optionsRadios3" value="no route">
                                No route
                            </label>
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
        // function toggleExcelImport() {
        //     $('#excelImportBox').toggle();
        // }
        $('#start-time').datetimepicker({format: 'yyyy-mm-dd hh:ii'});
        $('#end-time').datetimepicker({format: 'yyyy-mm-dd hh:ii'});
    </script>
@endsection
