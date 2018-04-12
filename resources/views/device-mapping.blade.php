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
<div class="pull-right"><button class="btn btn-primary" disabled><i class="fas fa-upload"></i>&nbsp; Import from Excel</button></div>
@endsection

@section('main-content')
    @include('partials/alerts')
    <div class="container-flex">
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
                            <th>Status</th>
                            <th style="width: 64px;">&nbsp;</th>
                            {{-- <th style="width: 40px">Label</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <form method="post" action="{{url('/')}}/event/{{$event_id}}/device-mapping/add">
                                {{ csrf_field() }}
                                <td><input class="form-control" name="device_id" placeholder="Device ID"></td>
                                <td>
                                    <select name="athlete_id" class="form-control">
                                        @foreach ($athletes as $athlete)
                                            <option value="{{$athlete->athlete_id}}">{{$athlete->first_name}} {{$athlete->last_name}} (Bib: {{$athlete->bib_number}}, Athlete ID: {{$athlete->athlete_id}})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="far fa-clock"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right" name="start_time" id="start-time" autocomplete="off" placeholder="yyyy-mm-dd hh:mm">
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="far fa-clock"></i>
                                        </div>
                                        <input type="text" class="form-control pull-right" name="end_time" id="end-time" autocomplete="off" placeholder="yyyy-mm-dd hh:mm">
                                    </div>
                                </td>
                                <td>
                                    <select name="status" class="form-control">
                                        <option value="visible">Visible</option>
                                        <option value="hidden">Hidden</option>
                                    </select>
                                </td>
                                <td><button type="submit" class="btn btn-primary">Add</button></td>
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
                                        <span>{{$device->first_name}} {{$device->last_name}} (Bib: {{$device->bib_number}}, Athlete ID: {{$athlete->athlete_id}})</span>
                                    </td>
                                    <td class="athlete_select" style="display: none;">
                                        <select name="athlete_id" class="form-control">
                                            @foreach ($athletes as $athlete)
                                                <option value="{{$athlete->athlete_id}}" {{($athlete->athlete_id == $device->athlete_id) ? 'selected' : ''}}>{{$athlete->first_name}} {{$athlete->last_name}} (Bib: {{$device->bib_number}}, Athlete ID: {{$athlete->athlete_id}})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="far fa-clock"></i>
                                            </div>
                                            <input type="text" class="form-control pull-right edit-start-time" name="start_time" id="start-time" autocomplete="off" value="{{$device->start_time}}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="far fa-clock"></i>
                                            </div>
                                            <input type="text" class="form-control pull-right edit-end-time" name="end_time" id="end-time" autocomplete="off" value="{{$device->end_time}}">
                                        </div>
                                    </td>
                                    <td class="status">
                                        <span><i class="fas fa-{{($device->status == 'visible' ? 'eye' : 'eye-slash')}}"></i> {{ucfirst($device->status)}}</span>
                                        <select name="status" class="form-control" style="display: none;">
                                            <option value="visible" {{($device->status == 'visible' ? 'selected' : '')}}>Visible</option>
                                            <option value="hidden" {{($device->status == 'hidden' ? 'selected' : '')}}>Hidden</option>
                                        </select>
                                    </td>
                                    <td><button type="button" class="edit-btn btn btn-default">Edit</button><button type="submit" class="btn btn-default" style="display: none;">Save</button></td>
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
            $(this).next().show();
        })


        $('#start-time, .edit-start-time').datetimepicker({format: 'yyyy-mm-dd hh:ii'});
        $('#end-time, edit-end-time').datetimepicker({format: 'yyyy-mm-dd hh:ii'});
    </script>
@endsection
