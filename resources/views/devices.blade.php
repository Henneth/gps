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
                            <th style="width: 12%">Device ID</th>
                            <th>Bib Number</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Country</th>
                            <th>Colour Code</th>
                            <th>Status</th>
                            <th style="width: 64px;">&nbsp;</th>
                            {{-- <th style="width: 40px">Label</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($devices as $key => $device)
                            <tr>
                                <form method="post" action="{{url('/')}}/event/{{$event_id}}/device-mapping/edit">
                                    <td class="text" data-name="device_id" data-placeholder="Device ID" data="{{$device->device_id}}">{{$device->device_id}}</td>
                                    <td class="text" data-name="bib_number" data-placeholder="Bib Number" data="{{$device->bib_number}}">{{$device->bib_number}}</td>
                                    <td class="text" data-name="first_name" data-placeholder="First Name" data="{{$device->first_name}}">{{$device->first_name}}</td>
                                    <td class="text" data-name="last_name" data-placeholder="Last Name" data="{{$device->last_name}}">{{$device->last_name}}</td>
                                    <td class="country_code" data-code="{{$device->country_code}}"><span>{{$device->country_code}}</span><div style="display: none;">@include('partials/countries-dropdown')</div></td>
                                    <td class="colour_code">
                                        @if ($device->colour_code)
                                            <div style="padding-left: 4px; background: {{$device->colour_code}}">{{$device->colour_code}}</div>
                                        @endif
                                        <input type="color" id="html5colorpicker" class="form-control" onchange="clickColor(0, -1, -1, 5)" name="colour_code" value="{{!empty($device->colour_code) ? $device->colour_code : '#0000ff'}}" style="display: none; width:100%;">
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
                        <tr>
                            <form method="post" action="{{url('/')}}/event/{{$event_id}}/device-mapping/add">
                                {{ csrf_field() }}
                                <td><input class="form-control" name="device_id" placeholder="Device ID"></td>
                                <td><input class="form-control" name="bib_number" placeholder="Bib Number"></td>
                                <td><input class="form-control" name="first_name" placeholder="First Name"></td>
                                <td><input class="form-control" name="last_name" placeholder="Last Name"></td>
                                <td>@include('partials/countries-dropdown')</td>
                                <td><input type="color" id="html5colorpicker" class="form-control" onchange="clickColor(0, -1, -1, 5)" name="colour_code" value="#0000ff" style="width:100%;"></td>
                                <td>
                                    <select name="status" class="form-control">
                                        <option value="visible">Visible</option>
                                        <option value="hidden">Hidden</option>
                                    </select>
                                </td>
                                <td><button type="submit" class="btn btn-primary">Add</button></td>
                            </form>
                        </tr>
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
                var name = $(this).attr('data-name');
                var data = $(this).attr('data');
                var placeholder = $(this).attr('data-placeholder');
                var value = $(this).text();
                var html = '<input class="form-control" name="' + name + '" placeholder="' + placeholder + '" value="' + value + '">';
                $(this).html(html);
            });
            var countryCode = form.find('.country_code').attr('data-code');
            form.find('.country_code span').hide();
            form.find('.country_code div').show();
            form.find('.country_code div select').val(countryCode);
            form.find('.colour_code div').hide();
            form.find('.colour_code input').show();
            form.find('.status span').hide();
            form.find('.status select').show();
            $(this).next().show();
        })
    </script>
@endsection
