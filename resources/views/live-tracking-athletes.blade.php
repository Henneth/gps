@extends('app')

@section('htmlheader_title')
    Live Tracking
@endsection

@section('contentheader_title')
    Live Tracking <small>{{$event->event_name}}</small>
@endsection

@section('main-content')
<div class="container-flex">

    <div class="nav-tabs-custom">
        @include('live-tracking-tabbar')
        <div class="tab-content">
            @if ($event->event_type == "fixed route")
                <div class="elevation-section tab-pane <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 1 ? 'active' : '');} else{} ?>" id="elevationChart" style="width:100%; height:100%;"></div>
            @endif
            <div class="profile-section tab-pane <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 2 ? 'active' : '');} else{} ?>">
                {{-- <p style="color: blue;">Maximum visible athletes at a time: 10</p> --}}
                <table id="profile-table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="line-height: 32px;">Bib Number</th>
                            <th style="line-height: 32px;">First Name</th>
                            <th style="line-height: 32px;">Last Name</th>
                            <th style="line-height: 32px;">Chinese Name</th>
                            <th style="line-height: 32px;">Country Code</th>
                            <th style="line-height: 32px;">Visibility (Max:20)&nbsp;&nbsp;<button id='resetLocalstorage' type="button" class="btn btn-default" style="padding: 1px 7px;">Reset</button></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1 ?>
                        @foreach($profile as $i)
                        <tr>
                            <td>{{$i->bib_number}}</td>
                            <td>{{$i->first_name}}</td>
                            <td>{{$i->last_name}}</td>
                            <td>{{$i->zh_full_name}}</td>
                            <td>{{$i->country_code}}</td>
                            <td>
                                <div>
                                    <input class="tgl tgl-ios check" data-id="{{$i->bib_number}}" id="{{$count}}" type="checkbox"  {{($i->status == "visible") ? ' checked="checked" ' :''}}/>
                                    <label class="tgl-btn" for="{{$count}}"></label>
                                </div>
                            </td>
                        </tr>
                        <?php $count++ ?>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@section('css')
    <style>
        #map {
            height:80vh;
            width: 100%;
        }
        .label_content{
            position:relative;
            border-radius: 4px;
            padding:4px;
            color:#ffffff;
            background-color: red;
            font-size: 12px;
            width: 100%;
            line-height: 20px;
            text-align: center;
            top: -8px;
        }

        .label_content:after {
            content:'';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -4px;
            width: 0;
            height: 0;
            border-top: solid 8px red;
            border-left: solid 4px transparent;
            border-right: solid 4px transparent;
        }


        .chart-info-window {
            padding: 16px;
            width: 160px;
            /* font-family="Arial"; */
            font-size: 14px;
            stroke-width:1;
            stroke:#3366cc;
        }
        .chart-info-window hr.end:last-child {
            display: none;
        }
    </style>
@endsection

@section('js')
    <script>
        var profile = {!! json_encode($profile) !!};

        var url_string = window.location.href; //window.location.href
        var url = new URL(url_string);

        $('#chart').click(function(){
            window.location.assign(url.origin+url.pathname+'?tab=1');
        })
        $('#profile-tab').click(function(){
            window.location.assign(url.origin+url.pathname+'?tab=2');
        })
        $('#home-tab').click(function(){
            window.location.assign(url.origin+url.pathname+'?tab=0');
        })

        $(document).ready(function() {
            $('#profile-table').DataTable({
                'columnDefs': [
                    { 'orderable': false, 'targets': 1 },
                    { 'orderable': false, 'targets': 2 },
                    { 'orderable': false, 'targets': 3 },
                    { 'orderable': false, 'targets': 4 },
                    { 'orderable': false, 'targets': 5 }
                ]
            });
        } );

        // Loacl Storage checks browser support
        if (typeof(Storage) !== "undefined") {
            var dataID = localStorage.getItem("visibility{{$event_id}}");

            // check localStorage existing
            if (dataID) {
                // console.log(dataID);

                // json decode localStorage
                var array = jQuery.parseJSON( dataID );

                // Clean all default atrr "checked"
                $('.tgl').removeAttr('checked');

                for (var i = array.length - 1; i >= 0; i--) {
                    $('.tgl[data-id="'+array[i]+'"]').prop("checked","checked");
                }
            }
        }

        $('.check').click(function(){
            var array = [];
            // check bib_number in localStorage, "ON" data will be save in localStorage
            var temp = localStorage.getItem("visibility{{$event_id}}");
            if(temp == null) {
                for (var i = 0; i < profile.length; i++) {
                    if (profile[i]['status'] == 'visible'){
                        array.push(profile[i]['bib_number']);
                    }
                }

            } else {
                var array = jQuery.parseJSON( temp );
            }

            if ($(this).is(':checked')) {
                var bib_number = this.getAttribute("data-id");
                var index = array.indexOf(bib_number);
                if (index < 0) {
                    array.push(bib_number);
                }
            } else {
                var bib_number = this.getAttribute("data-id");
                var index = array.indexOf(bib_number);
                if (index >= 0) {
                    array.splice(index, 1);
                }
            }
            // json encode
            var json = JSON.stringify(array);
            // store in localStorage
            localStorage.setItem("visibility{{$event_id}}", json);
        });

        // empty localStorage and redirect to map tab
        $('#resetLocalstorage').click(function(){
            localStorage.removeItem("visibility{{$event_id}}");
            location.reload();
        });

    </script>

@endsection
