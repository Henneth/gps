@extends('app')

@section('htmlheader_title')
    Device Mapping
@endsection

@section('contentheader_title')
    Device Mapping
@endsection

@section('main-content')
<div class="container-flex">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Devices</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 12%">Device ID</th>
                        <th>Bib Number</th>
                        <th>Given Name</th>
                        <th>Family Name</th>
                        <th>Show on public map</th>
                        {{-- <th style="width: 40px">Label</th> --}}
                    </tr>
                    @foreach ($devices as $key => $device)
                        <tr>
                            <td>{{$device->device_id}}</td>
                            <td>{{$device->bib_number}}</td>
                            <td>{{$device->given_name}}</td>
                            <td>{{$device->family_name}}</td>
                            <td>{{($device->show_in_public) ? 'true' : 'false'}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- /.box-body -->
        <div class="box-footer clearfix">
            <button type="submit" class="btn btn-primary">Save</button>
            <ul class="pagination pagination-sm no-margin pull-right">
                <li><a href="#">«</a></li>
                <li><a href="#">1</a></li>
                <li><a href="#">2</a></li>
                <li><a href="#">3</a></li>
                <li><a href="#">»</a></li>
            </ul>
        </div>
    </div>
</div>
@endsection
