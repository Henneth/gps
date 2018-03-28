@extends('app')

@section('htmlheader_title')
    View All Events
@endsection

@section('contentheader_title')
    View All Events
@endsection

@section('main-content')
    <div class="container-flex">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Events</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Event Name</th>
                            {{-- <th style="width: 40px">Label</th> --}}
                        </tr>
                        <tr>
                            <td>1</td>
                            <td><a href="{{url('/')}}/event/1">Dummy Event</a></td>
                            {{-- <td><span class="badge bg-red">55%</span></td> --}}
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">
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
