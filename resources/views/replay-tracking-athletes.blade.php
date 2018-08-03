@extends('app')

@section('htmlheader_title')
    Replay Tracking
@endsection

@section('contentheader_title')
    Replay Tracking
@endsection

@section('main-content')
<div class="loading" id="loading" style="display: none;">
    <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
</div>
<div>
    <div class="nav-tabs-custom">
        @include('replay-tracking-tabbar')
        <div class="tab-content">
            @if($event->event_type =='fixed route')
                <div  class="elevation-section tab-pane <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 1 ? 'active' : '');} else{} ?>" >
                    <div id="elevationChart" style="width:100%; height:100%;"></div>
                </div>
            @endif
            <div class="profile-section tab-pane <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 2 ? 'active' : '');} else{} ?>" >
                <table id="profile-table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Bib Number</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Chinese Name</th>
                            <th>Country Code</th>
                            <th>Visibility (Max:20)<div id='cleanLocalStorage'><i class="fas fa-info-circle"></i><button type="button" class="reset-btn btn btn-primary">Reset</button></th>
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

@section('js')
    <script>
        var profile = {!! json_encode($profile) !!};
        // console.log(profile);

        $(function () {
            var url_string = window.location.href; //window.location.href
            var url = new URL(url_string);

            // Flip tags
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
                    // console.log(array);
                } else {
                    var bib_number = this.getAttribute("data-id");
                    var index = array.indexOf(bib_number);
                    if (index >= 0) {
                        array.splice(index, 1);
                    }
                    // console.log(array);
                }
                    // console.log(array);
                // var array = jQuery.parseJSON( temp );


                // create array
                // // max 10
                // if($('.tgl:checked').size()>20){
                //     $(this).prop('checked', false);
                //     return;
                // }
                //
                // $('.tgl').each(function() {
                //     if ($(this).is(":checked")) {
                //         array.push($(this).attr("data-id"));
                //     }
                // })

                // json encode
                var json = JSON.stringify(array);
                console.log(json);
                // store in localStorage
                localStorage.setItem("visibility{{$event_id}}", json);

            });

            // empty localStorage and redirect to map tab
            $('.reset-btn').click(function(){
                localStorage.removeItem("visibility{{$event_id}}");
                location.reload();
            });
        })
    </script>
@endsection

@section('css')
    <style>
        #map {
            height:80vh;
            width: 100%;
        }

        #cleanLocalStorage {
            display: inline-block;
        }
        .reset-btn {
            display: none;
        }
        #cleanLocalStorage:hover .reset-btn{
            display: inline-block;
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

        .flex-container {
            display: flex;
        }
        .replay-controls {
            margin-right: 4px;
        }
        .slider-wrapper {
            padding: 4px 20px;
            flex-grow: 100;
        }
        .slider.slider-horizontal {
            width: 100%;
        }
        .slider-handle {
            position: absolute;
            width: 20px;
            height: 20px;
            background-color: #444;
            -webkit-box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05);
            -moz-box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05);
            opacity: 1;
            border: 0px solid transparent;
        }
        .slider-handle.round {
            -webkit-border-radius: 20px;
            -moz-border-radius: 20px;
            border-radius: 20px;
        }
        .slider-disabled .slider-selection {
            opacity: 0.5;
        }

        #red .slider-selection {
            background: #f56954;
        }

        #blue .slider-selection {
            background: #3c8dbc;
        }

        #green .slider-selection {
            background: #00a65a;
        }

        #yellow .slider-selection {
            background: #f39c12;
        }

        #aqua .slider-selection {
            background: #00c0ef;
        }

        #purple .slider-selection {
            background: #932ab6;
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
