@extends('app')

@section('htmlheader_title')
    Live Tracking
@endsection

@section('contentheader_title')
    Live Tracking <small>{{$event->event_name}}</small>
@endsection

@section('main-content')
    <div class="container-flex">
        <div class="loading" id="loading" style="display: none;">
            <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
        </div>
        <div class="nav-tabs-custom">
            @include('live-tracking-tabbar')
            <div class="tab-content">
                <table id="checkpoint-table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="line-height: 32px;">Athletes</th>
                            <?php $count=1 ?>
                            @foreach ($checkpoint as $index => $value)
                                @if( ($index != 0) && ($value->display == 1) )
                                    <th style="line-height: 32px;">CP {{$count}} {{$value->checkpoint_name ? '('.$value->checkpoint_name.')' : ''}}</th>
                                    <?php $count++ ?>
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@section('css')
    <style></style>
@endsection

@section('js')
    <script>
        // init global variables
        var localStorageArray; // store "ON" bib_number, data retrive from localStorage

        // check bib_number in localStorage, "ON" data will be saved in localStorage
        var temp = localStorage.getItem("visibility{{$event_id}}");
        var array = jQuery.parseJSON( temp );
        localStorageArray = array;
        // console.log(localStorageArray);
        $(document).ready(function() {
            var table = $('#checkpoint-table').DataTable({
                "ajax": {
                    "url": "{{url('/')}}/event/{{$event_id}}/live-tracking/checkpoint-table",
                    "data": {'bib_numbers': localStorageArray ? JSON.stringify(localStorageArray) : null},
                    "type": "GET",
                },
                "scrollX": true,
                "ordering": false,
                "searching": false,
                "bLengthChange": false,
            });

            setInterval( function () {
                table.ajax.reload();
            }, 60000 );
        });




        // function pollData(firstTime = false) {
        //     $.ajax({
        //         type:"get",
        //         url:"{{url('/')}}/event/{{$event_id}}/live-tracking/poll",
        //         data: {'bib_numbers': localStorageArray ? JSON.stringify(localStorageArray) : null},
        //         dataType:"json",
        //         success:function(ajax_data) {
        //             if (firstTime) {
        //                 $('#loading').fadeOut('slow',function(){$(this).remove();});
        //             }
        //             data = ajax_data;
        //             console.log('polling...');
        //             console.log(data);
        //             for (var bib_number in data) {
        //                 var first_name = data[bib_number]['athlete']['first_name'];
        //                 var last_name = data[bib_number]['athlete']['last_name']
        //                 // if (object.hasOwnProperty(variable)) {
        //                 //
        //                 // }
        //                 $("#checkpoint-table").find('tbody').append('<tr><td><b>'+bib_number+'</b>&nbsp;&nbsp;'+ first_name+' '+last_name+'</td></tr>');
        //             }
        //         // $("#checkpoint-table").find('tbody').append('<tr><td>'+bib_number+'</td></tr>');
        //
        //         },
        //         error:function() {
        //             $('#loading').fadeOut('slow',function(){$(this).remove();});
        //         }
        //     });
        // }
        // // Execute the setInterval function without delay the first time
        // $('#loading').show();
        // pollData(true);
        // setInterval(pollData, 5000);//time in milliseconds


        var url_string = window.location.href; //window.location.href
        var url = new URL(url_string);
        // console.log(url.origin+url.pathname);
        $('#chart').click(function(){
            window.location.assign(url.origin+url.pathname+'?tab=1');
        })
        $('#profile-tab').click(function(){
            window.location.assign(url.origin+url.pathname+'?tab=2');
        })
        $('#checkpoint-tab').click(function(){
            window.location.assign(url.origin+url.pathname+'?tab=3');
        })
        $('#home-tab').click(function(){
            window.location.assign(url.origin+url.pathname+'?tab=0');
        })

    </script>

@endsection