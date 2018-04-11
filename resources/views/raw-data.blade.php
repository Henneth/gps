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
                <table id="table1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>
                                <div class="form-group" style="width: 49.5%">
                                    <div class="input-group" style="width: 100%">
                                        <div class="input-group-addon" style="width: 16%">
                                            <span>From</span>
                                        </div>
                                        <input type="text" class="form-control" id="time-from" autocomplete="off" placeholder="yyyy-mm-dd hh:mm">
                                    </div>
                                </div><!--
                                --><div class="form-group" style="width: 49.5%; margin-left: 1%;">
                                    <div class="input-group" style="width: 100%">
                                        <div class="input-group-addon" style="width: 16%">
                                            <span>To</span>
                                        </div>
                                        <input type="text" class="form-control" id="time-to" autocomplete="off" placeholder="yyyy-mm-dd hh:mm">
                                    </div>
                                </div>
                            </th>
                            <th></th>
                            <th>
                                <select class="device_list form-control" style="width: 100%;">
                                    <option></option>
                                    @foreach($deviceID as $id)
                                        <option>{{$id->device_id}}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th></th>
                        <tr>
                            <th>Timestamp</th>
                            <th>Received At</th>
                            <th>
                                <span style="padding-right: 8px;">Device ID</span>
                            </th>
                            <th>Longitude</th>
                            <th>Latitude</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $datum)
                            <tr>
                                <td>{{$datum->datetime}}</td>
                                <td>{{$datum->created_at}}</td>
                                {{-- <td>{!! nl2br(e(str_replace(" ", " &nbsp;", $datum->raw))) !!}</td> --}}
                                <td>{{$datum->device_id}}</td>
                                <td>{{$datum->longitude_final}}</td>
                                <td>{{$datum->latitude_final}}</td>
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

@section('js')
    <script>
    $(function () {
        /* Custom filtering function which will search data in column four between two values */
        $.fn.dataTable.ext.search.push(
            function( settings, data, dataIndex ) {
                var from = new Date( $('#time-from').val());
                var to = new Date( $('#time-to').val());
                console.log(from);
                console.log(to);
                var timestamp = new Date( data[0] ) || 0; // use data for the age column
         
                if ( 
                    (timestamp <= to ) ||
                     ( from <= timestamp ) ||
                     ( from <= timestamp   && timestamp <= to ) )
                {
                    return true;
                }
                return false;
            }
        );

        var table = $('#table1').DataTable({
            'paging'      : true,
            'lengthChange': false,
            'searching'   : true,
            'ordering'    : true,
            'info'        : true,
            'autoWidth'   : false,
            'pageLength'  : 50,
            'order'       : [
                [ 0, "desc"], 
                [ 1, "desc"]
            ],
            'dom' : "lrtip",
            'columnDefs': [
                { 'orderable': false, 'targets': 2 },
                { 'orderable': false, 'targets': 3 },
                { 'orderable': false, 'targets': 4 }
            ]
        })

        $('.device_list').change(function () {
            table
                .columns( 2 )
                .search( this.value )
                .draw();
        });


        $('#time-from').datetimepicker({format: 'yyyy-mm-dd hh:ii'});
        $('#time-to').datetimepicker({format: 'yyyy-mm-dd hh:ii'});



        $('#time-from, #time-to').change(function () {
            table.draw();
        });
    })

        // Select2
        // In your Javascript (external .js resource or <script> tag)
        $(document).ready(function() {
            $('.device_list').select2({
                placeholder: "Select device ID",
                allowClear: true
            });
        });
    </script>
@endsection
