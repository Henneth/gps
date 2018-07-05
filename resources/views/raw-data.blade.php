@extends('app')

@section('htmlheader_title')
    Raw Data
@endsection

@section('contentheader_title')
    Raw Data
@endsection

@section('contentheader_class')
    display-inline-block
@endsection
@section('contentheader_right')
<div class="pull-right">
    <form role="form" action="{{url('/')}}/raw-data/export-raw-data" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}
        <button type="submit" class="btn btn-success exportToExcel disabled"><i class="fas fa-download"></i>&nbsp; Export to Excel</button>
        <input type="hidden" id="time-from-value" name="time-from">
        <input type="hidden" id="time-to-value" name="time-to">
        <input type="hidden" id="deviceID-value" name="deviceID">
    </form>
</div>
@endsection

@section('main-content')
    <div class="container-flex">
        <div class="box">
            <!-- /.box-header -->
            <div class="box-body">
                <ul class="nav nav-tabs">
                    <li class="{{ (!isset($_GET['live'])) ? 'active' : ''}}" ><a href="{{url('/')}}/raw-data">Achive Data</a></li>
                    <li class="{{ (isset($_GET['live']) && $_GET['live'] == 1) ? 'active' : ''}}" ><a href="{{url('/')}}/raw-data?live=1">Live Data</a></li>
                </ul>
                <table id="table1" class="table table-bordered table-striped display responsive no-wrap" width="100%">
                    <thead>
                        <tr>
                            <th style="width: 36%">
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
                            <th></th>
                            <th>
                                <button style="font-size:14px;" class="btn btn-default low-battery-devices "><i class="fa fa-battery-quarter" style="color:#666"></i> <span style="color: #666; font-weight: bold"><20%</span></button>
                                <button style="font-size:14px; border-color: #8a8787; display:none;" class="btn btn-default close-low-battery-devices"><span>⨉</span>&nbsp;&nbsp;&nbsp;<i class="fa fa-battery-quarter" style="color:#666"></i> <span style="color: #666;font-weight: bold"><20%</span></button>
                            </th>
                        </tr>
                        <tr>
                            <th>Timestamp</th>
                            <th>Received At</th>
                            <th>Delay</th>
                            <th>
                                <span style="padding-right: 8px;">Device ID</span>
                            </th>
                            <th>Longitude</th>
                            <th>Latitude</th>
                            <th>Battery Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $datum)
                            <tr>
                                <td>{{$datum->datetime}}</td>
                                <td>{{$datum->created_at}}</td>
                                <td>{{$datum->delay}}</td>

                                {{-- <td>{!! nl2br(e(str_replace(" ", " &nbsp;", $datum->raw))) !!}</td> --}}
                                <td>{{$datum->device_id}}</td>
                                <td>{{$datum->longitude_final}}</td>
                                <td>{{$datum->latitude_final}}</td>
                                <td>{{$datum->battery_level}}</td>
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
                var tfrom = new Date( $('#time-from').val());
                var tto = new Date( $('#time-to').val());
                var ttfrom = tfrom instanceof Date && isNaN(tfrom.valueOf());
                var ttto = tto instanceof Date && isNaN(tto.valueOf());
                // console.log(ttfrom);
                // console.log(ttto);
                var timestamp = new Date( data[0] ) || 0; // use data for the Timestamp column

                if (
                        (ttfrom && ttto ) ||
                        (ttfrom && timestamp <= tto ) ||
                        ( tfrom <= timestamp  && ttto) ||
                        ( tfrom <= timestamp   && timestamp <= tto )
                    ) {
                    return true;
                }
                return false;
            }
        );

        var table = $('#table1').DataTable({
            'responsive'  : false,
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
                { 'orderable': false, 'targets': 4 },
                { 'orderable': false, 'targets': 5 },
                { 'orderable': false, 'targets': 6 },

            ]
        })


        $('.device_list').change(function () {
            table
                .columns( 3 )
                .search( this.value )
                .draw();
                // set or remove hidden input tags values
                if($('.device_list').val()) {
                    $('#deviceID-value').val($('.device_list').val());
                }else{
                    $('#deviceID-value').removeAttr('value');
                }
                // active Export to Excel btn
                if($('.device_list').val()) {
                    $('.exportToExcel').removeClass('disabled');
                }else{
                    $('.exportToExcel').addClass('disabled');
                }
        });


        $('#time-from').datetimepicker({format: 'yyyy-mm-dd hh:ii'});
        $('#time-to').datetimepicker({format: 'yyyy-mm-dd hh:ii'});

        $('#time-from, #time-to').change(function () {
            table.draw();
            // set or remove hidden input tags values
            if($('#time-from').val() && $('#time-to').val()) {
                $('#time-from-value').val($('#time-from').val());
                $('#time-to-value').val($('#time-to').val());
            }else{
                $('#time-from-value').removeAttr('value');
                $('#time-to-value').removeAttr('value');
            }
            // active Export to Excel btn
            if($('#time-from').val() && $('#time-to').val()) {
                $('.exportToExcel').removeClass('disabled');
            }else{
                $('.exportToExcel').addClass('disabled');
            }
        });


        // get the low battery level of device
        var dataArray = [];
        var data = table.rows( { filter : 'applied'} ).data(); // get exsited date from table
        $('.low-battery-devices').click(function(){
            $('.close-low-battery-devices').css('display','block');
            $('.low-battery-devices').css('display','none');
            // get data after sorting from table
            // data = table.rows( { filter : 'applied'} ).data();
            for (var i = 0; i < data.length; i++) {
                if (data[i][6] == '20%' || data[i][6] == '10%') {
                    var check = false;

                    for (var j = 0; j < dataArray.length; j++) {
                        if (dataArray[j]['device_id'] == data[i][3] && dataArray[j]['battery_level'] == data[i][6]){
                            check = true;
                            // convert the format, replace the older one to the newer after comparison.
                            var arrayDateTime = new Date( dataArray[j]['data'][0] );
                            var dataDateTime= new Date( data[i][0] );
                            if (arrayDateTime < dataDateTime){
                                dataArray[j]['data'] = data[i];
                            }
                            break; // terminate
                        }
                    }
                    // if value is not exsited, add it into array
                    if (check == false) {
                        var tempArray = [];
                        tempArray['device_id'] = data[i][3];
                        tempArray['battery_level'] = data[i][6];
                        tempArray['data'] = data[i];
                        dataArray.push(tempArray);
                    }
                }
            }

            //https://stackoverflow.com/questions/27778389/how-to-manually-update-datatables-table-with-new-json-data
            table.clear(); // clean table
            for (var i = 0; i < dataArray.length; i++) {
                table.row.add(dataArray[i]['data']); // add data to rows
            }
            table.draw(); // Draw once all updates are done

        });

        $('.close-low-battery-devices').click(function(){
            $('.close-low-battery-devices').css('display','none');
            $('.low-battery-devices').css('display','block');
            table.clear(); // clean table
            table.rows.add(data); // add data to rows
            table.draw(); // draw table
        });

        // Select2
        $('.device_list').select2({
            placeholder: "Select device ID",
            allowClear: true
        });

    })
    </script>
@endsection
