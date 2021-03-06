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
            <div class="map-section tab-pane <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 0 ? 'active' : '');} else{echo 'active';} ?>">
                <div class="form-group" style="color: #666; float: left;">Athletes' latest locations from <b>{{$event->datetime_from}}</b> to <b>{{$event->datetime_to}}</b></div>
                <div style="color: #666; float: right;">Elapsed Time: <b><span id="time"></span></b></div>
                <div class="clearfix"></div>
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
    <!-- Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4i5s_R4E6Y8c5m4pEVxeVQvCJorm4MaI&libraries=geometry"></script>

    <!-- RichMarker -->
    <script src="{{ asset('/js/richmarker-compiled.js') }}" type="text/javascript"></script>

    <script>
        var IsCP;
        var display;
        var map;
        var infowindow, infowindow2;
        var checkpoint;
        var markerList = []; //array to store marker
        var firstLoad = true;
        var localStorageArray; // store "ON" bib_number, data retrive from localStorage
        var tailArray = [];
        var data;
        var eventType;
        var athleteMarkers = [];

        eventType = '{{$event->event_type}}';
        checkpoint = {!! $checkpoint !!};

        function findObjectByKey(array, key, value) {
            for (var i = 0; i < array.length; i++) {
                if (array[i][key] === value) {
                    return array[i];
                }
            }
            return null;
        }


        function initMap() {

            @if ($route)

                // Function to add a marker to the map.
                function addMarker(map, content) {
                    // Add the marker at the clicked location, and add the next-available label
                    // from the array of alphabetical characters.

                    var borderStyle = '<style>.id' + content['athlete']['bib_number'] + '.label_content:after { border-top: solid 8px #' + content['athlete']['colour_code'] + '; }</style>';
                    var marker = new RichMarker({
                        map: map,
                        flat: true,
                        position: new google.maps.LatLng(parseFloat(content['data'][0]['latitude']), parseFloat(content['data'][0]['longitude'])),
                        content: borderStyle + '<div><div class="id' + content['athlete']['bib_number'] + ' label_content" style="background-color: #' + content['athlete']['colour_code'] + '">' + content['athlete']['bib_number']
                        + '</div></div>',
                        data: content
                    });

                    google.maps.event.addListener(marker, 'click', (function (marker) {
            			return function () {
                            var html = '<div>Bib Number: <b>' + marker.data['athlete']['bib_number'] + '</b></div>';
                            if( marker.data['athlete']['first_name'] ){ html += '<div>First Name: <b>' + marker.data['athlete']['first_name'] + '</b></div>'; }
                            if( marker.data['athlete']['last_name'] ){ html += '<div>Last Name: <b>' + marker.data['athlete']['last_name'] + '</b></div>'; }
                            if( marker.data['athlete']['zh_full_name'] ){ html += '<div>Chinese Name: <b>' + marker.data['athlete']['zh_full_name'] + '</b></div>'; }
                            if( marker.data['athlete']['country'] ){ html += '<div>Country: <b>' + marker.data['athlete']['country'] + '</b></div>'; }
                            // html += '<div>Device ID: <b>' + marker.data['athlete']['device_id'] + '</b></div>';
                            html += '<div>Location: <b>' + parseFloat(marker.position.lat()).toFixed(6) + ', ' + parseFloat(marker.position.lng()).toFixed(6) + '</b></div>';

                            if (eventType == "fixed route"){
                                if (marker.data['distances'] && marker.data['distances'].length > 0) {
                                    var currentRouteIndex = marker.data['distances'].length - 1;
                                    html += '<div>Distance: <b>' + marker.data['distances'][currentRouteIndex]['distance_from_start'].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' m' + '</b></div>';
                                }

                                // if there is checkpoint time
                                if ( marker.data['reachedCheckpoint'] && marker.data['reachedCheckpoint'].length > 0){

                                    html += '<hr style="margin-top: 8px; margin-bottom: 8px;">';
                                    var checkpointTimes = marker.data['reachedCheckpoint'];

                                    // get the last reached checkpoint number
                                    var currentCheckpointNo = checkpointTimes[checkpointTimes.length-1]['checkpoint_id'] - 1;
                                    // get last checkpoint number
                                    var lastCheckpointNo = checkpoint[checkpoint.length-1]['checkpoint_no'];
                                    var count = 1 ;
                                    for (var i = 0; i < checkpointTimes.length; i++) {

                                        var checkpoint_no = checkpointTimes[i]['checkpoint_id'] - 1;
                                        if (checkpoint[checkpoint_no]['display'] == 1) {
                                            if (lastCheckpointNo == checkpoint_no) {
                                                html += '<div>Finish: <b>'+ checkpointTimes[i]['datetime'] + '</b></div>';
                                            } else {
                                                if ( checkpoint[checkpoint_no]['checkpoint_name'] ) {
                                                    html += '<div>' + checkpoint[checkpoint_no]['checkpoint_name'] + ' (CP' + count + '): <b>'+ checkpointTimes[i]['datetime'] + '</b></div>';
                                                } else {
                                                    html += '<div>CP' + count + ': <b>'+ checkpointTimes[i]['datetime'] + '</b></div>';
                                                }
                                            }
                                            count++;
                                        }
                                        // console.log(marker.reachedCheckpoint[i]);
                                    }

                                    if (currentCheckpointNo < lastCheckpointNo) {
                                        // get the latest checkpoint datetime
                                        var currentCheckpointTime = new Date(checkpointTimes[checkpointTimes.length-1]['datetime']).getTime();
                                        // match then get min time of checkpoint
                                        var nextCheckpointMinTime = new Date('1970-01-01T' + checkpoint[currentCheckpointNo + 1]['min_time'] + 'Z').getTime();

                                        // checkpoint: number of checkpoints
                                        // checkpoint number greater than 2 can do time prediction of next checkpooint
                                        if ( checkpointTimes.length >= 2 ) {

                                            var fCheckpoint; // former Checkpoint
                                            var lCheckpoint; // later Checkpoint
                                            var lCheckpointMinTime, fCheckpointTime, lCheckpointTime;
                                            // console.log(checkpointTimes);
                                            var SumOfSpeedRatios = 0;
                                            var SpeedRatioCount = 0;
                                            for (var i = 1; i < checkpointTimes.length; i++) {
                                                var former_checkpoint_id = i + 1;
                                                var former_checkpoint_no = i;
                                                if (findObjectByKey(checkpointTimes, 'checkpoint_id', former_checkpoint_id) && findObjectByKey(checkpointTimes, 'checkpoint_id', former_checkpoint_id + 1) && checkpoint[former_checkpoint_no + 1]['min_time'] != null) {
                                                    fCheckpointTime = new Date(findObjectByKey(checkpointTimes, 'checkpoint_id', former_checkpoint_id)['datetime']).getTime();
                                                    lCheckpointTime = new Date(findObjectByKey(checkpointTimes, 'checkpoint_id', former_checkpoint_id + 1)['datetime']).getTime();
                                                    lCheckpointMinTime = new Date('1970-01-01T' + checkpoint[former_checkpoint_no + 1]['min_time'] + 'Z').getTime();
                                                    SumOfSpeedRatios += (nCheckpointTime-lCheckpointTime) / lCheckpointMinTime;
                                                    SpeedRatioCount++;
                                                }

                                            }

                                            if (SpeedRatioCount > 0) {
                                                var tempPredictTime = SumOfSpeedRatios / SpeedRatioCount * nextCheckpointMinTime + currentCheckpointTime;

                                                var predictTime= new Date(tempPredictTime).toLocaleTimeString();

                                                var predictDate = new Date(tempPredictTime).toISOString().split('T')[0];

                                                html += '<div style="color:blue;"><br>Predicted time for next checkpoint: <b>' + predictDate +" "+ predictTime + '</b></div>';
                                            }

                                        }

                                    }

                                }
                            }

            				infowindow.setContent(html);
            				infowindow.open(map, marker);
            			}
            		})(marker));

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
                        IsCP = route[key]["is_checkpoint"] || key == 0;
                        display = route[key]["display"] || key == 0;
                        addLatLngInit(IsCP, display, new google.maps.LatLng(gpxLat, gpxLng));
                    }

                    // Add labels/icons to route markers
                    var CPIndex = 1;
                    var DisplayCPIndex = 1;
                    for (var i = 1; i < markerList.length -1; i++) {
                        if (markerList[i].isCheckpoint) {
                            var marker = markerList[i];
                            if (marker.display == 1){
                                // console.log(pointOrder);
                                cpName = checkpoint[CPIndex]['checkpoint_name'];
                                marker.checkpointName = cpName;
                                marker.displayCPIndex = DisplayCPIndex;
                                marker.setLabel({text: ""+DisplayCPIndex, color: "white"});
                                marker.addListener('click', function() {
                                    if (this.checkpointName){
                                        var html = '<div><b>'+ this.checkpointName + ' (CP' + this.displayCPIndex + ')</b>'+ '</div>';
                                    }else {
                                        var html = '<div><b>'+'CP'+ this.displayCPIndex + '</b></div>';
                                    }
                                    infowindow2.setContent(html);
                                    infowindow2.open(map, this);
                                });
                                DisplayCPIndex++;
                            }
                            CPIndex++;
                        }
                    }

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

                // if (tail){
                //     var lineSymbol = {
                //         path: 'M 0,-1 0,1',
                //         strokeOpacity: 1,
                //         scale: 2
                //     };
                //     for (var i in tail) {
                //         var tailCoordinates = [];
                //
                //         var colourCode = tail[i][0]['colour_code'];
                //         for (var j = 0; j < tail[i].length; j++) {
                //             var gpxLat2 = parseFloat(tail[i][j]['latitude_final']);
                //             var gpxLng2 = parseFloat(tail[i][j]['longitude_final']);
                //             tailCoordinates.push({lat:gpxLat2 , lng:gpxLng2})
                //         }
                //         // console.log(colourCode);
                //         var tailPath = new google.maps.Polyline({
                //             path: tailCoordinates,
                //             geodesic: true,
                //             strokeColor: '#'+colourCode,
                //             strokeOpacity: 0,
                //             strokeWeight: 2,
                //             icons: [{
                //                 icon: lineSymbol,
                //                 offset: '0',
                //                 repeat: '10px'
                //             }],
                //         });
                //         tailPath.setMap(map);
                //         tailArray[i] = tailPath;
                //     }
                // }

            @else
                var central = {lat: 22.2816616, lng: 114.1577151};
                var map = new google.maps.Map(document.getElementById('map'), {
                    center: central,
                    zoom: 11
                });
            @endif


            // check bib_number in localStorage
            var temp = localStorage.getItem("visibility{{$event_id}}");
            var array = jQuery.parseJSON( temp );
            localStorageArray = array;
            // console.log(localStorageArray);

            // poll data
            function pollData(firstTime = false) {
                $.ajax({
                    type:"get",
                    url:"{{url('/')}}/event/{{$event_id}}/live-tracking/poll",
                    data: {'bib_numbers': localStorageArray ? JSON.stringify(localStorageArray) : null},
                    dataType:"json",
                    success:function(ajax_data) {
                        if (firstTime) {
                            $('#loading').fadeOut('slow',function(){$(this).remove();});
                        }
                        data = ajax_data;
                        console.log('polling...');

                        {{--// check bib_number in localStorage
                        var temp = localStorage.getItem("visibility{{$event_id}}");
                        var localStorageArray = jQuery.parseJSON( temp );--}}

                        // add markers / set positions
                        for (var key in data) {

                            // marker exists
                            if (athleteMarkers[key]) {
                                athleteMarkers[key].setPosition( new google.maps.LatLng(parseFloat(data[key]['data'][0]['latitude']), parseFloat(data[key]['data'][0]['longitude'])) );
                                athleteMarkers[key].data = data[key];

                            // marker does not exist
                            } else {
                                if (data[key]['data'] && data[key]['data'].length != 0) {
                                    athleteMarkers[key] = addMarker(map, data[key]);
                                    // // localStorage is not empty
                                    // if (temp !== null) {
                                    //     if (jQuery.inArray(key, localStorageArray) !== -1) {
                                    //         athleteMarkers[key] = addMarker(map, data[key]);
                                    //     }
                                    // // localStorage empty
                                    // } else {
                                    //     // check database visible setting
                                    //     if (data[key]['athlete']['status'] == "visible"){
                                    //         athleteMarkers[key] = addMarker(map, data[key]);
                                    //     }
                                    // }
                                }
                            }

                            // get athlete's colour_code
                            var colourCode = data[key]['athlete']['colour_code'];

                            // participants
                            if (firstTime) {
                                if(data[key]["athlete"]){
                                    $("#participants").append('<tr><td><span class="symbolStyle" style="color: '+'#'+colourCode +';">&#9632;</span></td><td>'+data[key]["athlete"]["bib_number"]+'</td><td>'+data[key]["athlete"]["first_name"]+' ' +data[key]["athlete"]["last_name"]+'</td></tr>');
                                }
                            }

                            // tail
                            if (eventType != "fixed route") {
                                // get tail data
                                var tail = data[key]['data'];

                                var tailCoordinates = []; // array to store all Lat & Lng of that athlete

                                // update tails
                                if (typeof(tail) !== "undefined" && tail){

                                    var lineSymbol = {
                                        path: 'M 0,-1 0,1',
                                        strokeOpacity: 1,
                                        scale: 2
                                    };
                                    for (var i in tail) {
                                        var gpxLat2 = parseFloat(tail[i]['latitude']);
                                        var gpxLng2 = parseFloat(tail[i]['longitude']);
                                        tailCoordinates.push({lat:gpxLat2 , lng:gpxLng2});
                                    }

                                    if (typeof(tailArray[key]) !== "undefined" && tailArray[key]){
                                        tailArray[key].setMap(null);
                                    }

                                    tailArray[key] = new google.maps.Polyline({
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

                                    tailArray[key].setMap(map);
                                }
                            }
                        }
                    },
                    error:function() {
                        $('#loading').fadeOut('slow',function(){$(this).remove();});
                    }
                });
            }
            // Execute the setInterval function without delay the first time
            $('#loading').show();
            pollData(true);
            setInterval(pollData, 3000);//time in milliseconds

        }

        initMap();

        function addLatLngInit(IsCP, display, position) {

            path = poly.getPath();

            // Because path is an MVCArray, we can simply append a new coordinate
            // and it will automatically appear.
            path.push(position);

            // Add a new marker at the new plotted point on the polyline.
            var marker = new google.maps.Marker({
                position: position,
                title: '#' + path.getLength(),
                map: (IsCP && display == 1) ? map : null,
                isCheckpoint: IsCP,
                display: display,
            });

            markerList.push(marker);
        }

        // Set the date we're counting from
        var countDateFrom = new Date("{{$event->datetime_from}}").getTime();
        // Set the date we're counting to
        var countDateTo = new Date("{{$event->datetime_to}}").getTime();
        // Update the count time every 1 second
        var x = setInterval(function() {
            // Get todays date and time
            var now = new Date().getTime();
            var elapsed = now - countDateFrom;
            var expired = countDateTo - now;

            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(elapsed / (1000 * 60 * 60 * 24));
            var hours = Math.floor((elapsed % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((elapsed % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((elapsed % (1000 * 60)) / 1000);

            // If the count day is over, write some text
            if (expired < 0) {
                clearInterval(x);
                document.getElementById("time").innerHTML = "Event Ended";
            } else {
                // Output the result in an element with id="time"
                document.getElementById("time").innerHTML = days + "d " + hours + "h "
                + minutes + "m " + seconds + "s ";
            }
        }, 1000);


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
