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
                                    <th scope="col">Name</th>
                                    <th scope="col">Bib Number</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id='participants'></tr>
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
        var map;
        var infowindow, infowindow2;
        var checkpointDistances;
        var markerList = []; //array to store marker
        var firstLoad = true;
        var showOffKey; // store "ON" device_id, data retrive from localStorage
        var tailArray = [];
        var data;
        var eventType;
        var athleteMarkers = [];

        eventType = '{{$event->event_type}}';
        checkpointDistances = {!! $checkpointDistances !!};

        function findObjectByKey(array, key, value) {
            for (var i = 0; i < array.length; i++) {
                if (array[i][key] === value) {
                    return array[i];
                }
            }
            return null;
        }


        function initMap() {

            @if ($checkpointDistances)

                // Function to add a marker to the map.
                function addMarker(map, content) {
                    // Add the marker at the clicked location, and add the next-available label
                    // from the array of alphabetical characters.

                    var borderStyle = '<style>.id' + content['athlete']['device_id'] + '.label_content:after { border-top: solid 8px #' + content['athlete']['colour_code'] + '; }</style>';
                    var marker = new RichMarker({
                        map: map,
                        flat: true,
                        position: new google.maps.LatLng(parseFloat(content['data'][0]['latitude_final']), parseFloat(content['data'][0]['longitude_final'])),
                        content: borderStyle + '<div><div class="id' + content['athlete']['device_id'] + ' label_content" style="background-color: #' + content['athlete']['colour_code'] + '">' + content['athlete']['bib_number']
                        + '</div></div>'
                    });

                    google.maps.event.addListener(marker, 'click', (function (marker) {
            			return function () {
                            var html = '<div>Bib Number: <b>' + content['athlete']['bib_number'] + '</b></div>';
                            if( content['athlete']['first_name'] ){ html += '<div>First Name: <b>' + content['athlete']['first_name'] + '</b></div>'; }
                            if( content['athlete']['last_name'] ){ html += '<div>Last Name: <b>' + content['athlete']['last_name'] + '</b></div>'; }
                            if( content['athlete']['zh_full_name'] ){ html += '<div>Chinese Name: <b>' + content['athlete']['zh_full_name'] + '</b></div>'; }
                            if( content['athlete']['country'] ){ html += '<div>Country: <b>' + content['athlete']['country'] + '</b></div>'; }
                            html += '<div>Device ID: <b>' + content['athlete']['device_id'] + '</b></div>';

                            if (eventType == "fixed route"){
                                if ( marker ) { // update
                                    html += '<div>Location: <b>' + parseFloat(marker.position.lat()).toFixed(6) + ', ' + parseFloat(marker.position.lng()).toFixed(6) + '</b></div>';
                                    if( content['distances']){
                                        var currentRouteIndex = content['distances'].length - 1;

                                        html += '<div>Distance: <b>' + content['distances'][currentRouteIndex]['distance'].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' m' + '</b></div>';
                                    }
                                } else{ // initialize
                                    html += '<div>Location: <b>' + parseFloat(content['data'][0]['latitude_final']).toFixed(6) + ', ' + parseFloat(content['data'][0]['longitude_final']).toFixed(6) + '</b></div>';
                                    if( content['distances']){
                                        var currentRouteIndex = content['distances'].length - 1;

                                        html += '<div>Distance: <b>' + content['distances'][currentRouteIndex]['distance'].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' m' + '</b></div>';
                                    }
                                }
                                // if there is checkpoint time
                                if ( content['checkpointData'] ){

                                    html += '<hr style="margin-top: 8px; margin-bottom: 8px;">';

                                    var checkpointTimes = content['checkpointData'];


                                    // get the latest checkpoint index, is a number
                                    var currentCheckpoint = checkpointTimes[checkpointTimes.length-1]['checkpoint'];
                                    // get last checkpoint index from checkpoints' list, is a number
                                    var lastCheckpoint = checkpointDistances[checkpointDistances.length-1]['checkpoint'];

                                    for (var i = 0; i < checkpointTimes.length; i++) {
                                        if (lastCheckpoint == checkpointTimes[i]['checkpoint']) {
                                            html += '<div>Finish: <b>'+ checkpointTimes[i]['reached_at'] + '</b></div>';
                                        } else {
                                            if (checkpointTimes[i]['checkpoint_name']) {
                                                html += '<div>' + checkpointTimes[i]['checkpoint_name'] + ' (CP' + checkpointTimes[i]['checkpoint'] + '): <b>'+ checkpointTimes[i]['reached_at'] + '</b></div>';
                                            } else {
                                                html += '<div>CP' + checkpointTimes[i]['checkpoint'] + ': <b>'+ checkpointTimes[i]['reached_at'] + '</b></div>';
                                            }
                                        }
                                        // console.log(marker.checkpointData[i]);
                                    }

                                    if (currentCheckpoint < lastCheckpoint) {
                                        // get the latest checkpoint reached_at
                                        var currentCheckpointTime = new Date(checkpointTimes[checkpointTimes.length-1]['reached_at']).getTime();
                                        // match then get min time of checkpoints
                                        var nextCheckpointMinTime = new Date('1970-01-01T' + findObjectByKey(checkpointDistances, 'checkpoint', currentCheckpoint+1)['min_time'] + 'Z').getTime();

                                        // checkpointDistances: number of checkpoints
                                        // checkpoint number greater than 2 can do time prediction of next checkpooint
                                        if ( checkpointTimes.length >= 2 ) {

                                            var fCheckpoint; // new formerCheckpoint
                                            var nCheckpoint; // new next checkpoint
                                            var cCheckpoint; // new current checkpoint
                                            var fCheckpointMintime, nCheckpointMintime, cCheckpointMintime;
                                            var fCheckpointTime, nCheckpointTime, cCheckpointTime;
                                            // console.log(checkpointTimes);
                                            var SumOfSpeedRatios = 0;
                                            var SpeedRatioCount = 0;
                                            for (var i = 1; i < checkpointTimes.length; i++) {
                                                if (findObjectByKey(checkpointDistances, 'checkpoint', i) && findObjectByKey(checkpointTimes, 'checkpoint', i+1) && findObjectByKey(checkpointDistances, 'checkpoint', i+1)['min_time']) {
                                                    fCheckpointTime = new Date(findObjectByKey(checkpointTimes, 'checkpoint', i)['reached_at']).getTime(); // get reached_at
                                                    nCheckpointTime = new Date(findObjectByKey(checkpointTimes, 'checkpoint', i+1)['reached_at']).getTime(); // get reached_at

                                                    nCheckpointMintime = new Date('1970-01-01T' +  findObjectByKey(checkpointDistances, 'checkpoint', i+1)['min_time'] + 'Z').getTime();
                                                    SumOfSpeedRatios += (nCheckpointTime-fCheckpointTime) / nCheckpointMintime;
                                                    SpeedRatioCount++;
                                                    // console.log(nCheckpointTime);
                                                    // console.log(fCheckpointTime);
                                                    // console.log(nCheckpointMintime);
                                                }

                                            }

                                            if (SpeedRatioCount > 0) {
                                                var tempPredictTime = SumOfSpeedRatios / SpeedRatioCount * nextCheckpointMinTime + currentCheckpointTime;

                                                var predictTime= new Date(tempPredictTime).toLocaleTimeString();
                                                // console.log(predictTime);

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
                    // console.log(route);
                    for(var key in route){
                        gpxLat = parseFloat(route[key]["latitude"]);
                        gpxLng = parseFloat(route[key]["longitude"]);
                        IsCP = route[key]["is_checkpoint"] || key == 0;
                        addLatLngInit(IsCP, new google.maps.LatLng(gpxLat, gpxLng));
                    }

                    // Add labels/icons to route markers
                    var CPIndex = 1;

                    for (var i = 1; i < markerList.length -1; i++) {
                        if (markerList[i].isCheckpoint) {
                            var marker = markerList[i];

                            cpName = checkpointDistances[CPIndex-1]['checkpoint_name'];
                            // console.log(cpName);
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

                            markerList[i].setLabel({text: ""+CPIndex, color: "white"});
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


            // check device_id in localStorage
            var temp = localStorage.getItem("visibility{{$event_id}}");
            var array = jQuery.parseJSON( temp );
            showOffKey = array;

            function pollData(firstTime = false) {
                $.ajax({
                    type:"get",
                    url:"{{url('/')}}/event/{{$event_id}}/live-tracking/poll",
                    data: {'bib_numbers': showOffKey ? JSON.stringify(showOffKey) : null},
                    dataType:"json",
                    success:function(ajax_data) {
                        if (firstTime) {
                            $('#loading').fadeOut('slow',function(){$(this).remove();});
                        }
                        data = ajax_data;
                        console.log('polling...');
                        console.log(data);


                        var checkpointData = ajax_data['checkpointData'];
                        // console.log(data);

                        // check device_id in localStorage
                        var temp = localStorage.getItem("visibility{{$event_id}}");
                        var localStorageArray = jQuery.parseJSON( temp );
                        // console.log("localStorageArray"+localStorageArray);

                        // console.log(array);
                        for (var key in data) {
                            // marker exists
                            if (athleteMarkers[key]) {
                                athleteMarkers[key].setPosition( new google.maps.LatLng(parseFloat(data[key]['data'][0]['latitude_final']), parseFloat(data[key]['data'][0]['longitude_final'])) );
                                // athleteMarkers[device_id].profile = data[device_id];
                                // athleteMarkers[device_id].checkpointData = checkpointData[device_id];
                                // console.log(athleteMarkers[array[key]['device_id']]);

                            // marker does not exist
                            } else {
                                if (data[key]['data'] && data[key]['data'].length != 0) {
                                    // localStorage is not empty
                                    if (temp !== null) {
                                        if (jQuery.inArray(key, localStorageArray) !== -1) {
                                            athleteMarkers[key] = (addMarker(map, data[key]));
                                        }
                                    // localStorage empty
                                    } else {
                                        // check database visible setting
                                        if (data[key]['athlete']['status'] == "visible"){
                                            athleteMarkers[key] = (addMarker(map, data[key]));
                                        }
                                    }
                                }
                            }

                            // get athlete's colour_code
                            var colourCode = data[key]['athlete']['colour_code'];
                            colourCode = colourCode ? colourCode : '000000';

                            // participants
                            if (firstTime) {
                                if(data[key]["athlete"]){
                                    var d1 = document.getElementById('participants');
                                    d1.insertAdjacentHTML('afterend', '<td><span class="symbolStyle" style="color: '+'#'+colourCode +';">&#9632;</span></td><td>'+data[key]["athlete"]["first_name"]+' ' +data[key]["athlete"]["last_name"]+'</td><td>'+data[key]["athlete"]["bib_number"]+'</td>');
                                }
                            }

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

                                    var gpxLat2 = parseFloat(tail[i]['latitude_final']);
                                    var gpxLng2 = parseFloat(tail[i]['longitude_final']);
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
                    },
                    error:function() {
                        $('#loading').fadeOut('slow',function(){$(this).remove();});
                    }
                });
            }
            // Execute the setInterval function without delay the first time
            $('#loading').show();
            pollData(true);
            setInterval(pollData, 30000);//time in milliseconds

        }

        initMap();

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
