@extends('app')

@section('htmlheader_title')
    Device Mapping
@endsection

@section('contentheader_title')
    Device Mapping
@endsection

@section('contentheader_class')
    display-inline-block
@endsection

@section('contentheader_right')
<div class="pull-right"><button class="btn btn-primary" onclick="toggleExcelImport();return false;" {{$is_live? 'disabled' : ''}}><i class="fas fa-upload"></i>&nbsp; Import from Excel</button></div>
@endsection

@section('main-content')
    @include('partials/alerts')
    <div class="container-flex">
        <div id="excelImportBox" class="box box-primary" style="display: none;">
            <div class="box-header with-border">
                <h3 class="box-title">Import from Excel</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form role="form" action="{{url('/')}}/event/{{$event_id}}/device-mapping/import-from-excel" method="post" enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="box-body">
                    <div class="form-group">
                        <label for="excelFile">Excel file upload</label>
                        <input type="file" id="excelFile" name="fileToUpload">

                        <p class="help-block">.xls or .xlsx files only.</p>
                        <p class="help-block">Please refer to this example excel file for the required format: <a href={{ asset('examples/device_mapping_example.xlsx') }}>device_mapping_example.xlsx</a>.</p>
                    </div>
                </div>
                <!-- /.box-body -->

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary" {{$is_live? 'disabled' : ''}}>Submit</button>
                </div>
            </form>
        </div>
        <div class="box">
            <div class="box-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 15%">Device ID</th>
                            <th>Athlete</th>
                            {{-- <th>Bib Number</th>
                            <th>First Name</th>
                            <th>Last Name</th> --}}
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Default Visibility</th>
                            <th style="width: 64px;">&nbsp;</th>
                            {{-- <th style="width: 40px">Label</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <form method="post" action="{{url('/')}}/event/{{$event_id}}/device-mapping/add">
                                {{ csrf_field() }}
                                <td><input class="form-control" name="device_id" placeholder="Device ID" {{$is_live? 'disabled' : ''}}></td>
                                <td>
                                    <select name="athlete_bib_num" class="form-control" {{$is_live? 'disabled' : ''}}>
                                        @foreach ($athletes as $athlete)
                                            <option value="{{$athlete->bib_number}}">{{$athlete->first_name}} {{$athlete->last_name}} (Bib: {{$athlete->bib_number}})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="far fa-clock"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right start-time" name="start_time" autocomplete="off" placeholder="yyyy-mm-dd hh:mm" {{$is_live? 'disabled' : ''}}>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="far fa-clock"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right end-time" name="end_time" autocomplete="off" placeholder="yyyy-mm-dd hh:mm" {{$is_live? 'disabled' : ''}}>
                                    </div><div class="check" style="color:red; display: none;">End Time must be after Start Time!</div>
                                </td>
                                <td>
                                    <select name="status" class="form-control" {{$is_live? 'disabled' : ''}}>
                                        <option value="visible">Visible</option>
                                        <option value="hidden">Hidden</option>
                                    </select>
                                </td>
                                <td><button type="submit" class="btn btn-primary action-btn" {{$is_live? 'disabled' : ''}}>Add</button></td>
                            </form>
                        </tr>
                        @foreach ($devices as $key => $device)
                            <tr>
                                <form method="post" action="{{url('/')}}/event/{{$event_id}}/device-mapping/edit">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="device_mapping_id" value="{{$device->device_mapping_id}}">
                                    <td class="text">
                                        <span>{{$device->device_id}}</span>
                                        <input style="display: none;" class="form-control" name="device_id" placeholder="Device ID" value="{{$device->device_id}}"></td>
                                    <td class="athlete_tds">
                                        @if ($device->bib_number)
                                            <span>{{$device->first_name}} {{$device->last_name}} (Bib: {{$device->bib_number}})</span>
                                        @else
                                            <span style="color: #999">No matched athlete.</span>
                                        @endif
                                    </td>
                                    <td class="athlete_select" style="display: none;">
                                        <select name="athlete_bib_num" class="form-control">
                                            @foreach ($athletes as $athlete)
                                                <option value="{{$athlete->bib_number}}" {{($athlete->bib_number == $device->bib_number) ? 'selected' : ''}}>{{$athlete->first_name}} {{$athlete->last_name}} (Bib: {{$athlete->bib_number}})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="start-time-td">
                                        <span>{{$device->start_time}}</span>
                                        <div class="input-group" style="display: none;">
                                            <div class="input-group-addon">
                                                <i class="far fa-clock"></i>
                                            </div>
                                            <input type="text" class="form-control pull-right start-time" name="start_time"  autocomplete="off" value="{{$device->start_time}}">
                                        </div>
                                    </td>
                                    <td class="end-time-td">
                                        <span>{{$device->end_time}}</span>
                                        <div class="input-group" style="display: none;">
                                            <div class="input-group-addon">
                                                <i class="far fa-clock"></i>
                                            </div>
                                            <input type="text" class="form-control pull-right end-time" name="end_time"  autocomplete="off" value="{{$device->end_time}}">
                                        </div>
                                        <div class="check" style="color:red; display: none;">End Time must be after Start Time!</div>
                                    </td>
                                    <td class="status">
                                        <span><i class="fas fa-{{($device->status == 'visible' ? 'eye' : 'eye-slash')}}"></i> {{ucfirst($device->status)}}</span>
                                        <select name="status" class="form-control" style="display: none;">
                                            <option value="visible" {{($device->status == 'visible' ? 'selected' : '')}}>Visible</option>
                                            <option value="hidden" {{($device->status == 'hidden' ? 'selected' : '')}}>Hidden</option>
                                        </select>
                                    </td>
                                    <td><button type="button" class="edit-btn btn btn-default" {{$is_live? 'disabled' : ''}}>Edit</button><button type="submit" class="btn btn-default action-btn" style="display: none;" {{$is_live? 'disabled' : ''}}>Save</button></td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->
            {{-- <div class="box-footer clearfix">
                <button type="submit" class="btn btn-primary">Save</button>
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
        function toggleExcelImport() {
            $('#excelImportBox').toggle();
        }
        $(function(){

            $('.edit-btn').click(function() {
                $(this).hide();
                var form = $(this).parent().parent();
                form.find('.text').each(function() {
                    $(this).find('span').hide();
                    $(this).find('input').show();
                });
                var countryCode = form.find('.country_code').attr('data-code');
                form.find('.athlete_tds').hide();
                form.find('.athlete_select').show();
                form.find('.status span').hide();
                form.find('.status select').show();
                form.find('.start-time-td span').hide();
                form.find('.start-time-td div.input-group').show();
                form.find('.end-time-td span').hide();
                form.find('.end-time-td div.input-group').show();
                $(this).next().show();
            })



            $('.start-time').datetimepicker({format: 'yyyy-mm-dd hh:ii'});
            $('.end-time').datetimepicker({format: 'yyyy-mm-dd hh:ii'});


            $('.start-time, .end-time').change(function() {
                var form = $(this).parent().parent().parent();
                var startTime = new Date( form.find('.start-time').val());
                var endTime = new Date( form.find('.end-time').val());
                if (startTime > endTime){
                    form.find('.check').show();
                    form.find('.action-btn').prop("disabled", true);
                }else{
                    form.find('.check').hide();
                    form.find('.action-btn').prop("disabled", false);
                }
            });

        });


        // var ttfrom = startTime instanceof Date && isNaN(startTime.valueOf());
    </script>
@endsection
