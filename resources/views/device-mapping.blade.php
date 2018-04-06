@extends('app')

@section('htmlheader_title')
    Device Mapping
@endsection

@section('contentheader_title')
    Device Mapping
@endsection

@section('main-content')
    @include('partials/alerts')
    <div class="container-flex">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Devices</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 15%">Device ID</th>
                            <th style="width: 15%">Athlete ID</th>
                            <th>Bib Number</th>
                            <th>First Name</th>
                            <th>Last Name</th>
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
                                <td colspan="4">
                                    <select name="athlete_id" class="form-control">
                                        @foreach ($athletes as $athlete)
                                            <option value="{{$athlete->athlete_id}}">{{$athlete->athlete_id}}: {{$athlete->first_name}} {{$athlete->last_name}} (Bib: {{$athlete->bib_number}})</option>
                                        @endforeach
                                    </select>
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
                                        <span>{{$device->athlete_id}}</span>
                                    </td>
                                    <td class="athlete_tds">{{$device->bib_number}}</td>
                                    <td class="athlete_tds">{{$device->first_name}}</td>
                                    <td class="athlete_tds">{{$device->last_name}}</td>
                                    <td class="athlete_select" colspan="4" style="display: none;">
                                        <select name="athlete_id" class="form-control">
                                            @foreach ($athletes as $athlete)
                                                <option value="{{$athlete->athlete_id}}" {{($athlete->athlete_id == $device->athlete_id) ? 'selected' : ''}}>{{$athlete->athlete_id}}: {{$athlete->first_name}} {{$athlete->last_name}} (Bib: {{$athlete->bib_number}})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="status">
                                        <span>{{$device->status}}</span>
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
    </script>
@endsection
