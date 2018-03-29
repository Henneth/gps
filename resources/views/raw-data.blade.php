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
                        <th>Data</th>
                    </tr>
                    @foreach ($raw_data as $datum)
                        <tr>
                            <td>{{$datum->created_at}}</td>
                            <td>{!! nl2br(e(str_replace(" ", " &nbsp;", $datum->raw))) !!}</td>
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
