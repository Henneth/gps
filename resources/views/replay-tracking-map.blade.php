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
            <div class="flex-container form-group replay-controls-wrapper">
                <button type="button" class="replay-controls play btn btn-primary">Play</button>
                <button type="button" class="replay-controls pause btn btn-default" disabled>Pause</button>
                <button type="button" class="replay-controls stop btn btn-default" disabled>Stop</button>
                <div class="slider-wrapper">
                    <div>
                        <input type="text" value="" class="slider form-control" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="0" data-slider-orientation="horizontal" data-slider-selection="before" data-slider-tooltip="show" data-slider-id="aqua" autocomplete="off">
                    </div>
                    <div style="block">
                        <span>{{$event->datetime_from}}</span>
                        <span style="float:right;">{{$event->datetime_to}}</span>
                    </div>
                </div>
            </div>
            <div class="map-section tab-pane <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 0 ? 'active' : '');} else{echo 'active';} ?>" >
                <div style="position:relative;">
                    <div id="mySidenavBtn" class="sidenavbtn">
                        <span id="track-participants-title" onclick="openNav()"><i class="fa fa-users"></i>&nbsp;Track Participants</span>
                    </div>
                    <div id="mySidenav" class="sidenav">
                        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
                        <h4 style="padding:4px 8px; font-weight:600;">Track Participants</h4>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Race No.</th>
                                    <th scope="col">Name</th>
                                </tr>
                            </thead>
                            <tbody id="participants">
                            </tbody>
                        </table>
                    </div>
                    <div id="map"></div> {{-- google map here --}}
                </div>
                {{-- <div id="map"></div> --}}
            </div>
            @if($event->event_type =='fixed route')
                <div  class="elevation-section tab-pane <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 1 ? 'active' : '');} else{} ?>" >
                    <div id="elevationChart" style="width:100%; height:100%;"></div>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('js')
    <!-- Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4i5s_R4E6Y8c5m4pEVxeVQvCJorm4MaI&libraries=geometry"></script>

    <!-- RichMarker -->
    <script src="{{ asset('/js/richmarker-compiled.js') }}" type="text/javascript"></script>

    {{-- elevation chart --}}
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script>

        // init global variables
        var map;
        var infowindow, infowindow2;
        var currentRouteIndex;
        var markerList = []; //array to store marker
        var localStorageArray; // store "ON" bib_number, data retrive from localStorage
        var data;
        var eventType;
        var tailArray = [];

        eventType = '{{$event->event_type}}';
        checkpoint = {!! $checkpoint !!};
        console.log(checkpoint);

        function initMap() {

            @if ($route)

                // Function to add a marker to the map.
                function addMarker(map, content) {
                    var borderStyle = '<style>.id' + content['athlete']['bib_number'] + '.label_content:after { border-top: solid 8px #' + content['athlete']['colour_code'] + '; }</style>';
                    var marker = new RichMarker({
                        map: map,
                        flat: true,
                        position: new google.maps.LatLng(parseFloat(content['data'][0]['latitude']), parseFloat(content['data'][0]['longitude'])),
                        content: borderStyle + '<div><div class="id' + content['athlete']['bib_number'] + ' label_content" style="background-color: #' + content['athlete']['colour_code'] + '">' + content['athlete']['bib_number']
                        + '</div></div>'
                    });

                    // info window
                    google.maps.event.addListener(marker, 'click', function (marker) {
            			return function () {
                            console.log(marker);
                            var html = '<div>Bib Number: <b>' + content['athlete']['bib_number'] + '</b></div>';
                            if( content['athlete']['first_name'] ){ html += '<div>First Name: <b>' + content['athlete']['first_name'] + '</b></div>'; }
                            if( content['athlete']['last_name'] ){ html += '<div>Last Name: <b>' + content['athlete']['last_name'] + '</b></div>'; }
                            if( content['athlete']['zh_full_name'] ){ html += '<div>Chinese Name: <b>' + content['athlete']['zh_full_name'] + '</b></div>'; }
                            if( content['athlete']['country'] ){ html += '<div>Country: <b>' + content['athlete']['country'] + '</b></div>'; }
                            // html += '<div>Device ID: <b>' + content['athlete']['device_id'] + '</b></div>';
                            html += '<div>Location: <b>' + parseFloat(marker.position.lat()).toFixed(6) + ', ' + parseFloat(marker.position.lng()).toFixed(6) + '</b></div>';

                            if (eventType == "fixed route" && currentRouteIndex[content['athlete']['bib_number']]){
                                html += '<div>Distance: <b>' + currentRouteIndex[content['athlete']['bib_number']]['distance_from_start'].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' m' + '</b></div>';

                                // if there is checkpoint time
                                if (content['reachedCheckpoint'] && content['reachedCheckpoint'].length > 0) {

                                    html += '<hr style="margin-top: 8px; margin-bottom: 8px;">';
                                    var checkpointTimes = content['reachedCheckpoint'];

                                    // get last checkpoint number
                                    var lastCheckpointNo = checkpoint[checkpoint.length-1]['checkpoint_no'];

                                    for (var i = 0; i < checkpointTimes.length; i++) {
                                        var checkpoint_no = checkpointTimes[i]['checkpoint_id'] - 1;
                                        if (lastCheckpointNo == checkpoint_no) {
                                            html += '<div>Finish: <b>'+ checkpointTimes[i]['datetime'] + '</b></div>';
                                        } else {
                                            if ( checkpoint[checkpoint_no]['checkpoint_name'] ) {
                                                html += '<div>' + checkpoint[checkpoint_no]['checkpoint_name'] + ' (CP' + checkpoint_no + '): <b>'+ checkpointTimes[i]['datetime'] + '</b></div>';
                                            } else {
                                                html += '<div>CP' + checkpoint_no + ': <b>'+ checkpointTimes[i]['datetime'] + '</b></div>';
                                            }
                                        }
                                    }
                                }
                            }
            				infowindow.setContent(html);
            				infowindow.open(map, marker);
            			}
            		}(marker));

                    return marker;
                }

                // Map style
                var mapStyle = [
                    {
                        featureType: "transit",
                        elementType: "labels",
                        stylers: [
                            { visibility: "off" }
                        ]
                    },
                    {
                        featureType: "poi",
                        elementType: "labels",
                        stylers: [
                            { visibility: "off" }
                        ]
                    }
                ]

                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 13,
                    // center: {lat: 22.404767, lng: 114.1057550}
                    center: {lat: 22.3016616, lng: 114.1577151}
                });

                // set style
                map.set('styles', mapStyle);

                // empty markList for re-init map
                markerList = [];

                @if ($route)
                    // set route
                    poly = new google.maps.Polyline({
                        strokeColor: '#3d00f7',
                        strokeOpacity: 1,
                        strokeWeight: 3,
                        map: map
                    });

                    var route = {!!$route!!};

                    for(var key in route){
                        gpxLat = parseFloat(route[key]["latitude"]);
                        gpxLng = parseFloat(route[key]["longitude"]);
                        IsCP = (route[key]["is_checkpoint"] && route[key]["display"] == 1) || key == 0;
                        addLatLngInit(IsCP, new google.maps.LatLng(gpxLat, gpxLng));
                    }

                    // Add labels/icons to route markers
                    var CPIndex = 1;
                    for (var i = 1; i < markerList.length -1; i++) {
                        if (markerList[i].isCheckpoint) {
                            var marker = markerList[i];

                            cpName = route[CPIndex]['checkpoint_name'];
                            marker.checkpointName = cpName;
                            marker.checkpointIndex = CPIndex;
                            marker.setLabel({text: ""+CPIndex, color: "white"});
                            marker.addListener('click', function() {
                                if (this.checkpointName){
                                    var html = '<div><b>'+ this.checkpointName + ' (CP' + this.checkpointIndex + ')</b>'+ '</div>';
                                }else {
                                    var html = '<div><b>'+'CP'+ this.checkpointIndex + '</b></div>';
                                }
                                infowindow2.setContent(html);
                                infowindow2.open(map, this);
                            });
                            CPIndex++;
                        }
                    }
                    // console.log(markerList);
                    if ( markerList[markerList.length-1] ){
                        markerList[markerList.length-1].setLabel({text: "Fin.", color: "white", fontSize: "10px"});
                    }

                    if ( markerList[0] ){
                        markerList[0].setLabel({text: "Start", color: "white", fontSize: "10px"});
                    }

                    // map fit bounds
                    var bounds = new google.maps.LatLngBounds();
                    for (var i = 0; i < markerList.length; i++) {
                        bounds.extend(markerList[i].getPosition());
                    }
                    map.fitBounds(bounds);
                @endif

                // set InfoWindow pixelOffset
                infowindow = new google.maps.InfoWindow({
                    pixelOffset: new google.maps.Size(0, -36),
                });
                infowindow2 = new google.maps.InfoWindow();

                // Add athleteMarkers
                athleteMarkers = [];

                // check bib_number in localStorage, "ON" data will be save in localStorage
                var temp = localStorage.getItem("visibility{{$event_id}}");
                var array = jQuery.parseJSON( temp );
                localStorageArray = array;
                console.log(localStorageArray);

                $('#loading').show();
                $.ajax({
                    type:'get',
                    url:'{{url("/")}}/event/{{$event_id}}/replay-tracking/poll',
                    data: {'bib_numbers': localStorageArray ? JSON.stringify(localStorageArray) : null},
                    dataType: "json",
                    success:function(ajax_data) {
                        // console.log(ajax_data);
                        $('#loading').fadeOut('slow',function(){$(this).remove();});
                        data = ajax_data;
                        console.log('polling...');
                        console.log(data);

                        // add markers
                        for (var key in data) {
                            // console.log(data[key]);
                            if (data[key]['data'] && data[key]['data'].length != 0) {
                                athleteMarkers[key] = addMarker(map, data[key]);
                                // if (temp !== null) { // localStorage is not empty
                                //     if (jQuery.inArray(data[key]['athlete']['bib_number'], array) !== -1) {
                                //         athleteMarkers[key] = (addMarker(map, data[key]));
                                //     }
                                // } else {
                                //     if (data[key]['athlete']['status'] == "visible"){
                                //         athleteMarkers[key] = (addMarker(map, data[key]));
                                //     }
                                // }
                            }

                            // get athlete's colour_code
                            var colourCode = data[key]['athlete']['colour_code'];

                            if (data[key]['athlete']) {
                                $('#participants').append('<tr><td><span class="symbolStyle" style="color: '+'#'+colourCode +';">&#9632;</span></td><td>'+data[key]["athlete"]["bib_number"]+'</td><td>'+data[key]["athlete"]["first_name"]+' ' +data[key]["athlete"]["last_name"]+'</td></tr>');
                            }
                        }

                        currentRouteIndex = lastPositionData();
                    },
                    error:function() {
                        $('#loading').fadeOut('slow',function(){$(this).remove();});
                    }
                });




            @else
                var central = {lat: 22.2816616, lng: 114.1577151};
                var map = new google.maps.Map(document.getElementById('map'), {
                    center: central,
                    zoom: 11
                });
            @endif

        }

        initMap();

        //On button click, load new data
        function dataFilterByTime(datetime) {
            var athleteArray = [];

            for (var bib_number in data) {
                var routeIndexByBibNum = data[bib_number]['distances'];
                for (var j = 0; j < routeIndexByBibNum.length; j++) {
                    getTimeByBibNum = routeIndexByBibNum[j]['datetime'];
                    getTimeByBibNum = new Date(getTimeByBibNum).getTime() / 1000;
                    if (datetime >= getTimeByBibNum){
                        athleteArray[bib_number] = routeIndexByBibNum[j];
                    } else {
                        break;
                    }
                }
            }
            // console.log(athleteArray);
            return athleteArray;
        };

        // get last position of athlete
        function lastPositionData() {
            var athleteArray = [];

            if(typeof data !== 'undefined' && data){
                for (var bib_number in data) {
                    if( typeof data[bib_number] !== 'undefined' && data[bib_number] ){
                        var routeIndexByBibNum = data[bib_number]['distances'];
                        athleteArray[bib_number] = routeIndexByBibNum[routeIndexByBibNum.length-1];
                    }
                }
            }
            return athleteArray;
        };

        function addLatLngInit(IsCP, position) {

            path = poly.getPath();

            // Because path is an MVCArray, we can simply append a new coordinate
            // and it will automatically appear.
            path.push(position);

            if (IsCP) {
                // Add a new marker at the new plotted point on the polyline.
                var marker = new google.maps.Marker({
                    position: position,
                    title: '#' + path.getLength(),
                    map: map,
                    isCheckpoint: IsCP
                });
            } else {
                // Add a new marker at the new plotted point on the polyline.
                var marker = new google.maps.Marker({
                    position: position,
                    title: '#' + path.getLength(),
                    map: null,
                    isCheckpoint: IsCP
                });
            }
            markerList.push(marker);
        }
    </script>

    <script>
        $(function () {

            // Data
            timestamp_from = {{$timestamp_from}};
            timestamp_to = {{$timestamp_to}};

            /* BOOTSTRAP SLIDER */
            var slider = $('.slider')
            slider.slider({
                formatter: function(value) {
                    //value is percentage, need to convent it to time format
                    var offset = (timestamp_to - timestamp_from) * value / 100;
                    var time = offset + timestamp_from;
                    var dateString = moment.unix(time).format("YYYY-MM-DD HH:mm:ss");
                    // console.log(time);
                    return dateString;
                }
            })
            slider.slider().on('change', function (ev) {
                var pc = ev.value.newValue;
                updateMapMarkers(pc);
            });

            // Replay controls
            var intervalId = null;
            var varName = function(){
                var val = slider.slider('getValue');
                if (val >= 100) {
                    clearInterval(intervalId);

                    $('.replay-controls.stop').prop('disabled', 'disabled').removeClass('btn-primary').addClass('btn-default');
                    $('.replay-controls.play').prop("disabled", false).removeClass('btn-default').addClass('btn-primary');
                    $('.replay-controls.pause').prop('disabled', 'disabled').removeClass('btn-primary').addClass('btn-default');

                    slider.slider('setValue', 0);
                    updateMapMarkers(0);
                } else {
                    slider.slider('setValue', val+1);
                    updateMapMarkers(val+1);
                }
            };

            $('.replay-controls.play').click(function() {
                $(this).prop('disabled', 'disabled').removeClass('btn-primary').addClass('btn-default');
                $('.replay-controls.pause').prop("disabled", false).removeClass('btn-default').addClass('btn-primary');
                $('.replay-controls.stop').prop("disabled", false).removeClass('btn-default').addClass('btn-primary');
                intervalId = setInterval(varName, 50);
            });

            function updateMapMarkers(pc) {
                infowindow.close(map);
                var offset = (timestamp_to - timestamp_from) * pc / 100;
                var time = offset + timestamp_from;

                var dateString = moment.unix(time).format("YYYY-MM-DD HH:mm:ss");

                for (var bib_number in data) {

                    if (athleteMarkers[bib_number]) {

                        var markerHasData = false;
                        athleteMarkers[bib_number].setVisible(true);

                        // console.log(data[bib_number]['data']);
                        for (var i in data[bib_number]['data']) {
                            if (data[bib_number]['data'][i]['timestamp'] <= time) {
                                athleteMarkers[bib_number].setPosition( new google.maps.LatLng(parseFloat(data[bib_number]['data'][i]['latitude']), parseFloat(data[bib_number]['data'][i]['longitude'])) );
                                markerHasData = true;
                                break;
                            }
                        }

                        // tail
                        if (eventType != "fixed route") {
                            var tailCoordinates = []; // array to store all Lat & Lng of that athlete
                            var colourCode = data[bib_number]['athlete']['colour_code'];

                            var lineSymbol = {
                                path: 'M 0,-1 0,1',
                                strokeOpacity: 1,
                                scale: 2
                            };
                            for (var j = data[bib_number]['data'].length - 1; j > 0; j--) {
                                if (data[bib_number]['data'][j]['timestamp'] <= time) {
                                    var gpxLat2 = parseFloat(data[bib_number]['data'][j]['latitude']);
                                    var gpxLng2 = parseFloat(data[bib_number]['data'][j]['longitude']);
                                    tailCoordinates.push({lat:gpxLat2 , lng:gpxLng2});
                                }
                            }

                            if (typeof(tailArray[bib_number]) !== "undefined" && tailArray[bib_number]){
                                tailArray[bib_number].setMap(null);
                            }

                            tailArray[bib_number] = new google.maps.Polyline({
                                path: tailCoordinates,
                                geodesic: true,
                                strokeColor: '#'+colourCode,
                                strokeOpacity: 0,
                                strokeWeight: 2,
                                icons: [{
                                    icon: lineSymbol,
                                    offset: '0',
                                    repeat: '10px'
                                }],
                            });
                            tailArray[bib_number].setMap(map);
                        }

                        if (!markerHasData) {
                            athleteMarkers[bib_number].setVisible(false);
                        }
                    }
                }

                @if($event->event_type =='fixed route')
                    currentRouteIndex = dataFilterByTime(time);
                @endif
            }
            $('.replay-controls.pause').click(function() {
                $(this).prop('disabled', 'disabled').removeClass('btn-primary').addClass('btn-default');
                $('.replay-controls.play').prop("disabled", false).removeClass('btn-default').addClass('btn-primary');
                clearInterval(intervalId);
            });
            $('.replay-controls.stop').click(function() {
                $(this).prop('disabled', 'disabled').removeClass('btn-primary').addClass('btn-default');
                $('.replay-controls.play').prop("disabled", false).removeClass('btn-default').addClass('btn-primary');
                $('.replay-controls.pause').prop('disabled', 'disabled').removeClass('btn-primary').addClass('btn-default');
                clearInterval(intervalId);
                slider.slider('setValue', 0);
                updateMapMarkers(0);
            });

            var url_string = window.location.href; //window.location.href
            var url = new URL(url_string);

            // console.log(url.origin+url.pathname);

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

        })
        /* Set the width of the side navigation to 250px */
        function openNav() {
            document.getElementById("mySidenav").style.width = "400px";
            $("#mySidenavBtn").fadeOut();
        }

        /* Set the width of the side navigation to 0 */
        function closeNav() {
            document.getElementById("mySidenav").style.width = "0";
            $("#mySidenavBtn").fadeIn();
        }

    </script>
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
