@extends('app')

@section('htmlheader_title')
    Raw Data
@endsection

@section('contentheader_title')
    Raw Data
@endsection

@section('main-content')
<div class="container-flex">
    <div class="box">
        <!-- /.box-header -->
        <div class="box-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 12%">Received At</th>
                        <th>Device ID</th>
                        <th>Timestamp</th>
                        <th>Longitude</th>
                        <th>Latitude</th>
                        <th>Elevation</th>
                    </tr>
                    @foreach ($data as $datum)
                        <tr>
                            <td>{{$datum->created_at}}</td>
                            {{-- <td>{!! nl2br(e(str_replace(" ", " &nbsp;", $datum->raw))) !!}</td> --}}
                            <td>{{$datum->device_id}}</td>
                            <td>{{$datum->datetime}}</td>
                            <td>{{$datum->longitude_final}}</td>
                            <td>{{$datum->latitude_final}}</td>
                            <td>{{$datum->elevation}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- /.box-body -->
        {{-- <div class="box-footer clearfix">
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
