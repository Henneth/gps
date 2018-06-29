@extends('app')

@section('htmlheader_title')
    Live Tracking
@endsection

@section('contentheader_title')
    Live Tracking <small>{{$event->event_id == current_event ? $event->event_name : '' }}</small>
@endsection

@section('main-content')
<div class="container-flex">

    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li id="home-tab" <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 0 ? 'class="active"' : '');} else{echo 'class="active"';} ?> ><a href="#" data-toggle="tab">Map</a></li>
            <li id="chart" <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 1 ? 'class="active"' : '');} else{} ?> ><a href="#" data-toggle="tab">Elevation</a></li>
            <li id="profile-tab" <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 2 ? 'class="active"' : '');} else{} ?> ><a href="#" data-toggle="tab">Athletes</a></li>
        </ul>
        <div class="tab-content">
            <div class="map-section tab-pane <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 0 ? 'active' : '');} else{echo 'active';} ?>">
                <div class="form-group" style="color: #666; float: left;">Athletes' latest locations from <b>{{$event->datetime_from}}</b> to <b>{{$event->datetime_to}}</b></div>
                <div style="color: #666; float: right;">Elapsed Time: <b><span id="time"></span></b></div>
                <div id="map"></div> {{-- google map here --}}
            </div>
            <div class="elevation-section tab-pane <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 1 ? 'active' : '');} else{} ?>" id="elevationChart" style="width:100%; height:100%;"></div>
            <div class="profile-section tab-pane <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 2 ? 'active' : '');} else{} ?>">
                {{-- <p style="color: blue;">Maximum visible athletes at a time: 10</p> --}}
                <table id="profile-table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Bib Number</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Chinese Name</th>
                            <th>Country Code</th>
                            <th>Visibility (Max:10)</th>
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
                                    <input class="tgl tgl-ios check" data-id="{{$i->device_id}}" id="{{$count}}" type="checkbox"  {{($i->status == "visible") ? ' checked="checked" ' :''}}/>
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
    <!-- Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4i5s_R4E6Y8c5m4pEVxeVQvCJorm4MaI&libraries=geometry"></script>

    <!-- RichMarker -->
    <script src="{{ asset('/js/richmarker-compiled.js') }}" type="text/javascript"></script>

    {{-- elevation chart --}}
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script>
        // elevation chart
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(initChart);
        var chart;
        var chartOptions;
        var elevationData;
        var distance = 0;
        var IsCP;
        var map;
        var infowindow2;
        var elevations_global;
        var checkpointDistances;
        var markerList = []; //array to store marker
        var firstLoad = true;
        var minTime;
        var showOffKey; // store "ON" device_id, data retrive from localStorage
        var tailArray = [];


        function findObjectByKey(array, key, value) {
            for (var i = 0; i < array.length; i++) {
                if (array[i][key] === value) {
                    return array[i];
                }
            }
            return null;
        }

        function initMap() {

            data = {!! $data !!};

            // get min time of checkpoints
            minTime = {!! $minTime !!};

            getFinishedAthletes= {!!$getFinishedAthletes!!}

            @if ($data)

                // Function for marker symbol and color
                // function pinSymbol(color) {
                //     return {
                //         path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z',
                //         fillColor: color,
                //         fillOpacity: 1,
                //         strokeColor: '#fff',
                //         strokeWeight: 1,
                //         scale: 1.4,
                //         labelOrigin: new google.maps.Point(0, -29)
                //     };
                // }

                // Function to add a marker to the map.
                function addMarker(location, map, content) {
                    // Add the marker at the clicked location, and add the next-available label
                    // from the array of alphabetical characters.
                    // var marker = new google.maps.Marker({
                    //     position: new google.maps.LatLng(22.3016616, 114.1577151),
                    //     label: {
                    //         text: content['bib_number'],
                    //         fontSize: "10px"
                    //     },
                    //     icon: pinSymbol(content['colour_code']),
                    //     map: map
                    // });
                    var borderStyle = '<style>.id' + content['device_id'] + '.label_content:after { border-top: solid 8px #' + content['colour_code'] + '; }</style>';
                    var marker = new RichMarker({
                        map: map,
                        flat: true,
                        position: new google.maps.LatLng(parseFloat(content['latitude_final']), parseFloat(content['longitude_final'])),
                        content: borderStyle + '<div><div class="id' + content['device_id'] + ' label_content" style="background-color: #' + content['colour_code'] + '">' + content['bib_number']
                        + '</div></div>'
                    });

                    // get checkpoint distance relevant
                    var checkpointData = {!! $checkpointData !!};
                    // console.log(checkpointData);

                    google.maps.event.addListener(marker, 'click', (function (marker) {
            			return function () {
                            // console.log(marker);
                            var html = '<div>Bib Number: <b>' + content['bib_number'] + '</b></div>';
                            if( content['first_name'] ){ html += '<div>First Name: <b>' + content['first_name'] + '</b></div>'; }
                            if( content['last_name'] ){ html += '<div>Last Name: <b>' + content['last_name'] + '</b></div>'; }
                            if( content['zh_full_name'] ){ html += '<div>Chinese Name: <b>' + content['zh_full_name'] + '</b></div>'; }
                            if( content['country'] ){ html += '<div>Country: <b>' + content['country'] + '</b></div>'; }
                            html += '<div>Device ID: <b>' + content['device_id'] + '</b></div>';

                            if ( marker.profile ) { // update
                                html += '<div>Location: <b>' + parseFloat(marker.profile['latitude_final']).toFixed(6) + ', ' + parseFloat(marker.profile['longitude_final']).toFixed(6) + '</b></div>';
                                html += '<div>Distance: <b>' + marker.profile['distance'].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' m' + '</b></div>';
                            } else{ // initialize
                                html += '<div>Location: <b>' + parseFloat(location['lat']).toFixed(6) + ', ' + parseFloat(location['lng']).toFixed(6) + '</b></div>';
                                html += '<div>Distance: <b>' + content['distance'].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' m' + '</b></div>';
                            }

                            // if there is checkpoint time
                            if ( marker.checkpointData || checkpointData[content['device_id']]){

                                html += '<hr style="margin-top: 8px; margin-bottom: 8px;">';

                                if ( marker.checkpointData ){ // update
                                    console.log('updated data');

                                    // show athletes' checkpoint time -- reached at
                                    var checkpointTimes = marker.checkpointData;

                                } else if (checkpointData[content['device_id']]) { // initialize
                                    console.log('initial data');

                                    // show athletes' checkpoint time -- reached at
                                    var checkpointTimes = checkpointData[content['device_id']];

                                }


                                // get the latest checkpoint number
                                var currentCheckpoint = checkpointTimes[checkpointTimes.length-1]['checkpoint'];
                                var lastCheckpoint = minTime[minTime.length-1]['checkpoint'];

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
                                    var nextCheckpointMinTime = new Date('1970-01-01T' + findObjectByKey(minTime, 'checkpoint', currentCheckpoint+1)['min_time'] + 'Z').getTime();

                                    // minTime: number of checkpoints
                                    // checkpoint number greater than 2 can do time prediction of next checkpooint
                                    if ( checkpointTimes.length >= 2 ) { // && currentCheckpoint < minTime.length

                                        var fCheckpoint; // new formerCheckpoint
                                        var nCheckpoint; // new next checkpoint
                                        var cCheckpoint; // new current checkpoint
                                        var fCheckpointMintime, nCheckpointMintime, cCheckpointMintime;
                                        var fCheckpointTime, nCheckpointTime, cCheckpointTime;
                                        // console.log(minTime);
                                        // console.log(checkpointTimes);
                                        var SumOfSpeedRatios = 0;
                                        var SpeedRatioCount = 0;
                                        for (var i = 1; i < checkpointTimes.length; i++) {
                                            if (findObjectByKey(minTime, 'checkpoint', i) && findObjectByKey(checkpointTimes, 'checkpoint', i+1) && findObjectByKey(minTime, 'checkpoint', i+1)['min_time']) {
                                                fCheckpointTime = new Date(findObjectByKey(checkpointTimes, 'checkpoint', i)['reached_at']).getTime(); // get reached_at
                                                nCheckpointTime = new Date(findObjectByKey(checkpointTimes, 'checkpoint', i+1)['reached_at']).getTime(); // get reached_at

                                                nCheckpointMintime = new Date('1970-01-01T' +  findObjectByKey(minTime, 'checkpoint', i+1)['min_time'] + 'Z').getTime();
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
                    var route = {!!$route->route!!};
                    // console.log(route);
                    for(var key in route){
                        gpxLat = parseFloat(route[key]["lat"]);
                        gpxLng = parseFloat(route[key]["lon"]);
                        IsCP = route[key]["isCheckpoint"] || key == 0;
                        addLatLngInit(IsCP, new google.maps.LatLng(gpxLat, gpxLng));
                    }

                    // Add labels/icons to route markers
                    var CPIndex = 1;
                    checkpointDistances = {!! $checkpointDistances !!};

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

                // Add athleteMarkers
                var athleteMarkers = [];

                // check device_id in localStorage
                var temp = localStorage.getItem("visibility{{$event_id}}");
                var array = jQuery.parseJSON( temp );
                showOffKey = array;
                // console.log(" array: " + showOffKey );


                for (var i = 0; i < data.length; i++) {
                    var location = {lat: parseFloat(data[i]['latitude_final']), lng: parseFloat(data[i]['longitude_final'])};

                    // localStorage is not empty
                    if (temp !== null) {
                        if (jQuery.inArray(data[i]['device_id'], array) !== -1) {
                            athleteMarkers[data[i]['device_id']] = (addMarker(location, map, data[i]));
                        }
                    // localStorage empty
                    } else {
                        // check database visible setting
                        if (data[i]['status'] == "visible"){
                            athleteMarkers[data[i]['device_id']] = (addMarker(location, map, data[i]));
                        }
                    }
                }

                var tail = {!!$tail!!};
                if (tail){
                    var lineSymbol = {
                        path: 'M 0,-1 0,1',
                        strokeOpacity: 1,
                        scale: 2
                    };
                    for (var i in tail) {
                        var tailCoordinates = [];

                        var colourCode = tail[i][0]['colour_code'];
                        for (var j = 0; j < tail[i].length; j++) {
                            var gpxLat2 = parseFloat(tail[i][j]['latitude_final']);
                            var gpxLng2 = parseFloat(tail[i][j]['longitude_final']);
                            tailCoordinates.push({lat:gpxLat2 , lng:gpxLng2})
                        }
                        // console.log(colourCode);
                        var tailPath = new google.maps.Polyline({
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
                        tailPath.setMap(map);
                        tailArray[i] = tailPath;
                    }
                }
                console.log(tailArray);

    // Final position
                // console.log(getFinishedAthletes);
                // if(getFinishedAthletes){
                //     for (var i = 0; i < getFinishedAthletes.length; i++) {
                //         var finishedDeviceID = getFinishedAthletes[i]['device_id'];
                //         console.log(athleteMarkers[getFinishedAthletes[i]['device_id']]);
                //         athleteMarkers[getFinishedAthletes[i]['device_id']].setPosition( new google.maps.LatLng(22.407490, 114.237020));
                //     }
                // }
                // athleteMarkers['4109212383'].setPosition( new google.maps.LatLng(22.407490, 114.237020));

            @else
                var central = {lat: 22.2816616, lng: 114.1577151};
                var map = new google.maps.Map(document.getElementById('map'), {
                    center: central,
                    zoom: 11
                });
            @endif

            setInterval(function()
            {
                $.ajax({
                    type:"get",
                    url:"{{url('/')}}/event/{{$event_id}}/live-tracking/poll",
                    dataType:"json",
                    success:function(data)
                    {
                        console.log('polling...');
                        var array = data['data'];
                        var checkpointData = data['checkpointData'];
                        var currentRouteIndex = data['currentRouteIndex'];
                        var tail = data['tail'];
                        // console.log(currentRouteIndex);


                        // automatically update chart tab --- if showOffKey is not null, localStorage will be used
                        var currentRouteIndex_filtered = [];

                        if (showOffKey !== null){
                            for (var i = 0; i < showOffKey.length; i++) {
                                // console.log("offKey: "+ showOffKey[i]);
                                for (var j = 0; j < currentRouteIndex.length; j++) {
                                    if( showOffKey[i] == currentRouteIndex[j]['device_id'] ){
                                        currentRouteIndex_filtered.push(currentRouteIndex[j]);
                                    }
                                }
                            }
                            // console.log(currentRouteIndex_filtered);
                        }else{
                            for(var i in array ){
                                // console.log(array);
                                for (var j = 0; j < currentRouteIndex.length; j++) {
                                    // console.log(currentRouteIndex[j]);

                                    if ( (array[i]['device_id'] == currentRouteIndex[j]['device_id']) && (array[i]['status'] == "visible") ){
                                        currentRouteIndex_filtered.push(currentRouteIndex[j]);
                                    }
                                }
                            }
                            // console.log(currentRouteIndex_filtered);
                        }
                        currentRouteIndex = currentRouteIndex_filtered;

                        drawChart(currentRouteIndex);


                        // check device_id in localStorage
                        var temp = localStorage.getItem("visibility{{$event_id}}");
                        var localStorageArray = jQuery.parseJSON( temp );
                        // console.log("localStorageArray"+localStorageArray);

                        // console.log(currentRouteIndex);
                        // console.log(array);
                        for (var key in array) {
                            // marker exists
                            if (athleteMarkers[array[key]['device_id']]) {
                                athleteMarkers[array[key]['device_id']].setPosition( new google.maps.LatLng(parseFloat(array[key]['latitude_final']), parseFloat(array[key]['longitude_final'])) );
                                athleteMarkers[array[key]['device_id']].profile = array[key];
                                athleteMarkers[array[key]['device_id']].checkpointData = checkpointData[array[key]['device_id']];
                                // console.log(athleteMarkers[array[key]['device_id']]);

                            // marker does not exist
                            } else {
                                var location = {lat: parseFloat(array[key]['latitude_final']), lng: parseFloat(array[key]['longitude_final'])};

                                // localStorage is not empty
                                if (temp !== null) {
                                    if (jQuery.inArray(array[key]['device_id'], localStorageArray) !== -1) {
                                        athleteMarkers[array[key]['device_id']] = (addMarker(location, map, array[key]));
                                    }
                                // localStorage empty
                                } else {
                                    // check database visible setting
                                    if (array[key]['status'] == "visible"){
                                        athleteMarkers[array[key]['device_id']] = (addMarker(location, map, array[key]));
                                    }
                                }
                            }
                        }


                        // update tails
                        if (tail){
                            var lineSymbol = {
                                path: 'M 0,-1 0,1',
                                strokeOpacity: 1,
                                scale: 2
                            };
                            for (var i in tail) {
                                var tailCoordinates = [];

                                var colourCode = tail[i][0]['colour_code'] ? tail[i][0]['colour_code'] : '000000';
                                for (var j = 0; j < tail[i].length; j++) {
                                    var gpxLat2 = parseFloat(tail[i][j]['latitude_final']);
                                    var gpxLng2 = parseFloat(tail[i][j]['longitude_final']);
                                    tailCoordinates.push({lat:gpxLat2 , lng:gpxLng2})
                                }
                                // console.log(colourCode);

                                tailArray[i].setMap(null);
                                tailArray[i] = new google.maps.Polyline({
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
                                tailArray[i].setMap(map);
                            }
                        }
                    }
                });
            }, 10000);//time in milliseconds

        }


        function initChart() {

            // Create an ElevationService.
            var elevator = new google.maps.ElevationService;

            // Draw the path, using the Visualization API and the Elevation service.
            displayPathElevation(path, elevator, map);
        }

        function displayPathElevation(path, elevator, map) {
            var tempArray = [];
            for (var key in path.b) {
                tempArray.push({'lat' : path.b[key].lat(), 'lng' : path.b[key].lng()});
            }

            // Create a PathElevationRequest object using this array.
            // Ask for 256 samples along that path.
            // Initiate the path request.
            elevator.getElevationAlongPath({
                'path': tempArray,
                'samples': 512
            }, plotElevation);

            // calculate the distance from point to point
            for (var i = 0; i < tempArray.length -1; i++) {
                var p1 = new google.maps.LatLng(tempArray[i]);
                var p2 = new google.maps.LatLng(tempArray[i+1]);
                distance += parseFloat(google.maps.geometry.spherical.computeDistanceBetween(p1, p2));
                // console.log(parseFloat(google.maps.geometry.spherical.computeDistanceBetween(p1, p2)));
            }
        }

        // Takes an array of ElevationResult objects, draws the path on the map
        // and plots the elevation profile on a Visualization API ColumnChart.
        function plotElevation(elevations, status) {
            elevations_global = elevations;
            chartDiv = document.getElementById('elevationChart');
            if (status !== 'OK') {
                // Show the error code inside the chartDiv.
                chartDiv.innerHTML = 'Cannot show elevation: request failed because ' +
                status;
                return;
            }
            // console.log(elevations);

            // Create a new chart in the elevationChart DIV.
            chart = new google.visualization.AreaChart(chartDiv);
            // Extract the data from which to populate the chart.
            // Because the samples are equidistant, the 'Sample'
            // column here does double duty as distance along the
            // X axis.

            // get athlethe's distance relevant
            var currentRouteIndex = {!! $currentRouteIndex !!};
            checkpointDistances = {!! $checkpointDistances !!};

            // console.log(currentRouteIndex);

            // chart tab --- if showOffKey is not null, localStorage will be used
            var currentRouteIndex_filtered = [];

            if (showOffKey !== null){
                for (var i = 0; i < showOffKey.length; i++) {
                    // console.log("offKey: "+ showOffKey[i]);
                    for (var j = 0; j < currentRouteIndex.length; j++) {
                        if( showOffKey[i] == currentRouteIndex[j]['device_id'] ){
                            currentRouteIndex_filtered.push(currentRouteIndex[j]);
                        }
                    }

                }
                // console.log(currentRouteIndex_filtered);
            }else{
                for(var i in data ){
                    // console.log(data);

                    for (var j = 0; j < currentRouteIndex.length; j++) {
                        // console.log(currentRouteIndex[j]);

                        if ( (data[i]['device_id'] == currentRouteIndex[j]['device_id']) && (data[i]['status'] == "visible") ){
                            currentRouteIndex_filtered.push(currentRouteIndex[j]);
                        }
                    }
                }
                // console.log(currentRouteIndex_filtered);
            }
            currentRouteIndex = currentRouteIndex_filtered;


            var chartHeight = $(window).height() * .8;

            // ticks calculation
            if (distance < 5000) {
                // unlikely cases
                var step = .1;
            } else if (distance < 10000) {
                var step = 1;
            } else if (distance < 100000) {
                var step = Math.ceil(distance / 10000);
            } else {
                var step = Math.ceil(distance / 100000);
            }

            // decap calculation
            // var tempNo = Math.round(distance / Math.pow(10,Math.floor(distance).toString().length-1));
            // if (tempNo > 5) {
            //     var step = Math.pow(10,Math.floor(distance).toString().length-1)/1000;
            // } else {
            //     var step = Math.pow(10,Math.floor(distance).toString().length-2)*tempNo/1000;
            // }
            var ticks = [];
            var current = step;
            while (current <= distance/1000) {
                ticks.push(current);
                current = current + step;
            }

            // Draw the chart using the data within its DIV.
            chartOptions = {
                // title: 'Event Elevation Chart',
                isStacked: true,
                height: chartHeight,
                legend: 'none',
                titleY: 'Elevation (m)',
                titleX: 'Distance (km)',
                chartArea: {
                    left: 60,
                    top: 60,
                    bottom: 80,
                    right: 36,
                },
                hAxis: {
                    ticks: ticks,
                    gridlines: {
                        color: 'transparent',
                    }
                },
                series: {
                    1: {
                        color: 'transparent',
                        annotations: {
                            stem: {
                                length: chartHeight * .1
                            },
                            textStyle: {
                                color: '#666'
                            }
                        }
                    }
                },
               // hAxis: { showTextEvery: 64,
               // slantedText:true, slantedTextAngle:45},
               // pointShape: { type: 'triangle', rotation: 180 },
               displayAnnotations: true,
               tooltip: {
                   isHtml: true
               }
            }
            drawChart(currentRouteIndex);
        }

        function drawChart(currentRouteIndex) {

            elevationData = new google.visualization.DataTable();
            elevationData.addColumn('number', 'Distance');
            elevationData.addColumn('number', 'Elevation');
            elevationData.addColumn({type: 'string', role:'annotation'});
            elevationData.addColumn({type: 'string', role:'annotationText', p: {html: true}});
            elevationData.addColumn('number', 'dummy');
            elevationData.addColumn({type: 'string', role:'tooltip', p: {html: true}});
            elevationData.addColumn({type: 'string', role:'annotation'});

            for (var i = 0; i < elevations_global.length; i++) {
                // the current athlete's distance between
                var dist = distance/elevations_global.length * i;
                var nextDist = distance/elevations_global.length * (i+1);

                var annotationStr = "";
                var athleteCount = 0;
                var str = "";
                str += 'Distance: <b>' + String((distance/elevations_global.length * i).toFixed(0)).replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' m</b><br/>Elevation: <b>' + elevations_global[i].elevation.toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' m</b><hr class="end" style="width: 100%; positive: absolute; margin-left: 0;">';
                for (var j = 0; j < currentRouteIndex.length; j++) {
                    var athletheDist = currentRouteIndex[j]['distance'];
                    var athletheDeviceID = currentRouteIndex[j]['device_id'];
                    var athleteBibNumber = currentRouteIndex[j]['bib_number'];
                    var athleteFirstName = currentRouteIndex[j]['first_name'];
                    var athleteLastName = currentRouteIndex[j]['last_name'];
                    var athleteChineseName = currentRouteIndex[j]['zh_full_name'];
                    var athleteColour = currentRouteIndex[j]['colour_code'];

                    if (dist <= athletheDist && athletheDist < nextDist){
                        str += 'Bib Number: <b>' + athleteBibNumber + '</b><br/>';
                        str += 'First Name: <b>' + athleteFirstName + '</b><br/>';
                        str += 'Last Name: <b>' + athleteLastName + '</b><br/><hr class="end" style="width: 100%; positive: absolute; margin-left: 0;">';

                        athleteCount++;
                        if (athleteCount == 1) {
                            annotationStr = athleteBibNumber;
                            colour = athleteColour;
                        } else {
                            annotationStr = '(' + athleteCount + ')';
                            colour = '';
                        }
                    }

                }
                // strDist = strDist.slice(0, -1);
                checkpoint = null;
                for (var key in checkpointDistances) {
                    if (dist <= checkpointDistances[key]['distance'] && checkpointDistances[key]['distance'] < nextDist){
                        // var checkpoint = String(checkpointDistances[key]['checkpoint']);
                        if (checkpointDistances[key]['checkpoint_name']) {
                            var checkpoint = String(checkpointDistances[key]['checkpoint_name']);
                        }else {
                            if (key == checkpointDistances.length -1) {
                                var checkpoint = String('Finish');
                            } else {
                                var checkpoint = String('CP'+checkpointDistances[key]['checkpoint']);
                            }
                        }
                        break;
                    }
                }

                if (annotationStr.length>0){
                    elevationData.addRow([parseInt(distance/elevations_global.length * i)/1000, elevations_global[i].elevation, annotationStr+"#"+colour, '<div class="chart-info-window">'+str+'</div>', 0, '<div class="chart-info-window">Distance: <b>'+String((distance/elevations_global.length * i).toFixed(0)).replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'m</b><br/>Elevation:<b>'+elevations_global[i].elevation.toFixed(0)+' m</b></div>', checkpoint ? checkpoint : null]);
                } else {
                    elevationData.addRow([parseInt(distance/elevations_global.length * i)/1000, elevations_global[i].elevation, null, null, 0, '<div class="chart-info-window">Distance: <b>'+String((distance/elevations_global.length * i).toFixed(0)).replace(/\B(?=(\d{3})+(?!\d))/g, ",")+' m</b><br/>Elevation: <b>'+elevations_global[i].elevation.toFixed(0)+' m</b></div>', checkpoint ? checkpoint : null ]);
                }
            }

            function updateAnnotationColourText() {
                Array.prototype.forEach.call(chartDiv.getElementsByTagName('text'), function (text, index) {
                    if (text.getAttribute('text-anchor') === 'middle' && text.getAttribute('fill') === '#3366cc') {
                        if (text.innerHTML.indexOf('#') >= 0) {
                            var info = text.innerHTML.split("#");
                            text.innerHTML = info[0];
                            text.setAttribute('fill', "#"+info[1]);
                        }
                    }
                });
            }
            google.visualization.events.addListener(chart, 'ready', updateAnnotationColourText);
            google.visualization.events.addListener(chart, 'onmouseover', updateAnnotationColourText);
            google.visualization.events.addListener(chart, 'onmouseout', updateAnnotationColourText);
            google.visualization.events.addListener(chart, 'select', updateAnnotationColourText);

            chart.draw(elevationData, chartOptions);
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


        function resizeChart () {
            chart.draw(elevationData, chartOptions);
        }
        if (document.addEventListener) {
            window.addEventListener('resize', resizeChart);
        }
        else if (document.attachEvent) {
            window.attachEvent('onresize', resizeChart);
        }
        else {
            window.resize = resizeChart;
        }

        var url_string = window.location.href; //window.location.href
        var url = new URL(url_string);
        var tabIndex = url.searchParams.get("tab");
        if (tabIndex == 2){
            $('.replay-controls-wrapper').hide();
        }
        // console.log(url.origin+url.pathname);

        $('#chart').click(function(){
            $('.map-section').removeClass('active');
            $('.profile-section').removeClass('active');
            window.location.assign(url.origin+url.pathname+'?tab=1');
            // $('.elevation-section').addClass('active');
            // resizeChart();
        })
        $('#profile-tab').click(function(){
            $('.elevation-section').removeClass('active');
            $('.map-section').removeClass('active');
            window.location.assign(url.origin+url.pathname+'?tab=2');
            // $('.profile-section').addClass('active');
        })
        $('#home-tab').click(function(){
            $('.elevation-section').removeClass('active');
            $('.profile-section').removeClass('active');
            window.location.assign(url.origin+url.pathname+'?tab=0');
            // $('.map-section').addClass('active');
            // initMap();
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

        // ios style botton
        $('.check').click(function(){
            // create array
            var array = [];
            if($('.tgl:checked').size()>10){
                $(this).prop('checked', false);
                return;
            }
            $('.tgl').each(function() {
                if ($(this).is(":checked")) {
                    array.push($(this).attr("data-id"));
                }
            })

            // json encode
            var json = JSON.stringify(array);

            // store in localStorage
            localStorage.setItem("visibility{{$event_id}}", json);

        })


    </script>

@endsection
