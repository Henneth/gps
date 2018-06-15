@extends('app')

@section('htmlheader_title')
    Replay Tracking
@endsection

@section('contentheader_title')
    Replay Tracking
@endsection

@section('main-content')
<div>

    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li id="home-tab" class="active"><a href="#" data-toggle="tab">Map</a></li>
            <li id="chart" ><a href="#" data-toggle="tab">Elevation</a></li>
            <li id="profile-tab" ><a href="#" data-toggle="tab">Athletes</a></li>
        </ul>
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
            <div class="map-section tab-pane active">
                <div id="map"></div>
            </div>
            <div  class="elevation-section tab-pane">
                <div id="elevationChart" style="width:100%; height:100%;"></div>
            </div>
            <div class="profile-section tab-pane">
                <table id="profile-table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Bib Number</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Chinese Name</th>
                            <th>Country Code</th>
                            <th>Visibility</th>
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

    // init global variables
    var map;
    var chart;
    var chartOptions;
    var elevationData;
    var distance = 0;
    var elevations_global;
    var infowindow;
    var checkpointData;
    var currentRouteIndex;
    var checkpointDistances;
    var markerList = []; //array to store marker
    var showOffKey; // store "ON" device_id, data retrive from localStorage


        function initMap() {

            data = {!! $data !!};
            // console.log(data);

            @if ($data)

                // Function to add a marker to the map.
                function addMarker(location, map, content) {
                    var borderStyle = '<style>.id' + content['device_id'] + '.label_content:after { border-top: solid 8px #' + content['colour_code'] + '; }</style>';
                    var marker = new RichMarker({
                        map: map,
                        flat: true,
                        position: new google.maps.LatLng(parseFloat(content['latitude_final']), parseFloat(content['longitude_final'])),
                        content: borderStyle + '<div><div class="id' + content['device_id'] + ' label_content" style="background-color: #' + content['colour_code'] + '">' + content['bib_number']
                        + '</div></div>'
                    });

                    // get checkpoint distance relevant
                    checkpointData = {!! $checkpointData !!};
                    checkpointDistances = {!! $checkpointDistances !!};

                    // info window
                    google.maps.event.addListener(marker, 'click', function (marker) {
            			return function () {
                            // console.log(currentRouteIndex);
                            var html = '<div>Bib Number: <b>' + content['bib_number'] + '</b></div>';
                            if( content['first_name'] ){ html += '<div>First Name: <b>' + content['first_name'] + '</b></div>'; }
                            if( content['last_name'] ){ html += '<div>Last Name: <b>' + content['last_name'] + '</b></div>'; }
                            if( content['zh_full_name'] ){ html += '<div>Chinese Name: <b>' + content['zh_full_name'] + '</b></div>'; }
                            html += '<div>Country: <b>' + content['country'] + '</b></div>';
                            html += '<div>Device ID: <b>' + content['device_id'] + '</b></div>';
                            html += '<div>Location: <b>' + parseFloat(marker.position.lat()).toFixed(6) + ', ' + parseFloat(marker.position.lng()).toFixed(6) + '</b></div>';
                            html += '<div>Distance: <b>' + currentRouteIndex[content['device_id']]['distance'].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' m' + '</b></div>';

                            if (checkpointData[content['device_id']]) {
                                html += '<hr style="margin-top: 8px; margin-bottom: 8px;">';
                                // show athlete reaches at checkpoint time
                                var checkpointTimes = checkpointData[content['device_id']];
                                for (var j = 0; j < checkpointTimes.length; j++) {
                                    html += '<div>Checkpoint ' + checkpointTimes[j]['checkpoint'] + ': <b>'+ checkpointTimes[j]['reached_at'] + '</b></div>';
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
                    var route = {!!$route->route!!};
                    // console.log(data);
                    for(var key in route){
                        gpxLat = parseFloat(route[key]["lat"]);
                        gpxLng = parseFloat(route[key]["lon"]);
                        IsCP = route[key]["isCheckpoint"] || key == 0;
                        addLatLngInit(IsCP, new google.maps.LatLng(gpxLat, gpxLng));
                    }

                    // Add labels/icons to route markers
                    var CPIndex = 1;
                    for (var i = 1; i < markerList.length; i++) {
                        if (markerList[i].isCheckpoint) {
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

                // Add athleteMarkers
                athleteMarkers = [];

                // check device_id in localStorage, "ON" data will be save in localStorage
                var temp = localStorage.getItem("visibility{{$event_id}}");
                var array = jQuery.parseJSON( temp );
                showOffKey = array;
                console.log(" array: " + showOffKey );

                // console.log(data);
                for (var key in data) {
                    // console.log(data[key][0]);
                    if (typeof data[key][0] != "undefined") {
                        var location = {lat: parseFloat(data[key][0]['latitude_final']), lng: parseFloat(data[key][0]['longitude_final'])};

                        if (temp !== null) { // localStorage is not empty
                            if (jQuery.inArray(data[key][0]['device_id'], array) !== -1) {
                                athleteMarkers[data[key][0]['device_id']] = (addMarker(location, map, data[key][0]));
                            }
                        } else {
                            if (data[key][0]['status'] == "visible"){
                                athleteMarkers[data[key][0]['device_id']] = (addMarker(location, map, data[key][0]));
                            }
                        }
                    }
                }


            @else
                var central = {lat: 22.2816616, lng: 114.1577151};
                var map = new google.maps.Map(document.getElementById('map'), {
                    center: central,
                    zoom: 11
                });
            @endif

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
                'samples': 216
            }, plotElevation);

            // calculate the distance from point to point
            for (var i = 0; i < tempArray.length -1; i++) {
                var p1 = new google.maps.LatLng(tempArray[i]);
                var p2 = new google.maps.LatLng(tempArray[i+1]);
                distance += parseFloat(google.maps.geometry.spherical.computeDistanceBetween(p1, p2));
                // console.log(parseFloat(google.maps.geometry.spherical.computeDistanceBetween(p1, p2)));
            }



            // console.log(tempArray);
            //
            // // var p1 = new google.maps.LatLng(45.463688, 9.18814);
            // var p2 = new google.maps.LatLng(46.0438317, 9.75936230000002);
            // console.log("p1 " + p1);
            // console.log("p2 " + p2);
            // alert(calcDistance(p1, p2));
            //
            // //calculates distance between two points in km's
            // function calcDistance(p1, p2) {
            //   return google.maps.geometry.spherical.computeDistanceBetween(p1, p2);
            // }
        }

        var routeIndexesByDevice;
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
            elevationData = new google.visualization.DataTable();
            elevationData.addColumn('number', 'Distance');
            elevationData.addColumn('number', 'Elevation');
            elevationData.addColumn({type: 'string', role:'annotation'});
            elevationData.addColumn({type: 'string', role:'annotationText', p: {html: true}});
            elevationData.addColumn('number', 'dummy');
            elevationData.addColumn({type: 'string', role:'tooltip', p: {html: true}});
            elevationData.addColumn({type: 'string', role:'annotation'});

            // get athlethe relavant
            routeIndexesByDevice = {!! $routeIndexesByDevice !!};

            // if showOffKey is not null, localStorage will be used
            var routeIndexesByDevice_filtered = [];
            if (showOffKey !== null){
                for (var i = 0; i < showOffKey.length; i++) {
                    showOffKey[i];
                    console.log(showOffKey[i]);
                    if(jQuery.inArray( showOffKey[i], routeIndexesByDevice )){
                        routeIndexesByDevice_filtered[showOffKey[i]] = routeIndexesByDevice[showOffKey[i]];
                    }
                }
                console.log(routeIndexesByDevice_filtered);
            }else{
                for(var i in data ){
                    if (jQuery.inArray( i, routeIndexesByDevice ) && typeof data[i][0] != "undefined" && data[i][0]['status'] == "visible"){
                        routeIndexesByDevice_filtered[i] = routeIndexesByDevice[i];
                    }
                }
                console.log(routeIndexesByDevice_filtered);
            }
            routeIndexesByDevice = routeIndexesByDevice_filtered;


            var chartHeight = $(window).height() * .8;

            // ticks calculation
            var tempNo = Math.round(distance / Math.pow(10,Math.floor(distance).toString().length-1));
            if (tempNo > 5) {
                var step = Math.pow(10,Math.floor(distance).toString().length-1);
            } else {
                var step = Math.pow(10,Math.floor(distance).toString().length-2)*tempNo;
            }
            var ticks = [];
            var current = step;
            while (current <= distance) {
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
                titleX: 'Distance (m)',
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
            currentRouteIndex = lastPositionData();
            drawChart(currentRouteIndex);
        }

        function drawChart(currentRouteIndex) {

            for (var i = 0; i < elevations_global.length; i++) {
                // the current athlete's distance between
                var dist = distance/elevations_global.length * i;
                var nextDist = distance/elevations_global.length * (i+1);

                var annotationStr = "";
                var athleteCount = 0;
                var str = "";
                str += 'Distance: <b>' + String((distance/elevations_global.length * i).toFixed(0)).replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' m</b><br/>Elevation: <b>' + elevations_global[i].elevation.toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' m</b><hr class="end" style="width: 100%; positive: absolute; margin-left: 0;">';
                for (var j in currentRouteIndex) {
                    var athletheDist = currentRouteIndex[j]['distance'];
                    var athletheDeviceID = currentRouteIndex[j]['device_id'];
                    var athleteBibNumber = currentRouteIndex[j]['bib_number'];
                    var athleteFirstName = currentRouteIndex[j]['first_name'];
                    var athleteLastName = currentRouteIndex[j]['last_name'];
                    var athleteChineseName = currentRouteIndex[j]['zh_full_name'];
                    var athleteColour = currentRouteIndex[j]['colour_code'];
                    // console.log(athletheDist);

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
                        var checkpoint = String(checkpointDistances[key]['checkpoint']);
                        break;
                    }
                }

                if (annotationStr.length>0){
                    elevationData.addRow([parseInt(distance/elevations_global.length * i), elevations_global[i].elevation, annotationStr+"#"+colour, '<div class="chart-info-window">'+str+'</div>', 0, '<div class="chart-info-window">Distance: <b>'+String((distance/elevations_global.length * i).toFixed(0)).replace(/\B(?=(\d{3})+(?!\d))/g, ",")+'m</b><br/>Elevation:<b>'+elevations_global[i].elevation.toFixed(0)+' m</b></div>', checkpoint ? checkpoint : null]);
                } else {
                    elevationData.addRow([parseInt(distance/elevations_global.length * i), elevations_global[i].elevation, null, null, 0, '<div class="chart-info-window">Distance: <b>'+String((distance/elevations_global.length * i).toFixed(0)).replace(/\B(?=(\d{3})+(?!\d))/g, ",")+' m</b><br/>Elevation: <b>'+elevations_global[i].elevation.toFixed(0)+' m</b></div>', checkpoint ? checkpoint : null ]);
                }
            }
            chart.draw(elevationData, chartOptions);

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

        }

        //On button click, load new data
        function dataFilterByTime(datetime) {
            var athleteArray = [];
            // console.log(routeIndexesByDevice[0]);
            for (var key in routeIndexesByDevice) {
                var routeIndexByDevice = routeIndexesByDevice[key];
                for (var j = 0; j < routeIndexByDevice.length; j++) {
                    getTimeByDevice = routeIndexByDevice[j]['reached_at'];
                    getTimeByDevice = new Date(getTimeByDevice).getTime() / 1000;
                    // console.log(getTimeByDevice);
                    // console.log(datetime);
                    if (datetime <= getTimeByDevice){
                        if (j == 0) {
                            athleteArray[key] = routeIndexByDevice[0];
                            break;
                        }
                        // console.log(routeIndexByDevice[j]);
                        athleteArray[key] = routeIndexByDevice[j-1];
                        break;
                    }
                }
            }
            // console.log(athleteArray);
            return athleteArray;
        };

        function lastPositionData() {
            var athleteArray = [];
            // console.log(routeIndexesByDevice[0]);
            for (var key in routeIndexesByDevice) {
                var routeIndexByDevice = routeIndexesByDevice[key];
                // console.log(routeIndexByDevice);
                athleteArray[key] = routeIndexByDevice[routeIndexByDevice.length-1];
            }
            // console.log(athleteArray);
            return athleteArray;
        };


        initMap();

        function resizeChart() {
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
            // console.log(dateString);

            for (var device_id in data) {
                if (athleteMarkers[device_id]) {
                    // console.log(device_id);
                    var markerHasData = false;
                    athleteMarkers[device_id].setVisible(true);
                    for (var i in data[device_id]) {
                        if (data[device_id][i]['timestamp'] <= time) {
                            // console.log(data[device_id][i]['timestamp']);
                            athleteMarkers[device_id].setPosition( new google.maps.LatLng(parseFloat(data[device_id][i]['latitude_final']), parseFloat(data[device_id][i]['longitude_final'])) );
                            markerHasData = true;
                            break;
                        }
                    }
                    if (!markerHasData) {
                        athleteMarkers[device_id].setVisible(false);
                    }
                }
            }

            // clear and redraw elevation chart
            elevationData = new google.visualization.DataTable();
            elevationData.addColumn('number', 'Distance');
            elevationData.addColumn('number', 'Elevation');
            elevationData.addColumn({type: 'string', role:'annotation'});
            elevationData.addColumn({type: 'string', role:'annotationText', p: {html: true}});
            elevationData.addColumn('number', 'dummy');
            elevationData.addColumn({type: 'string', role:'tooltip', p: {html: true}});
            elevationData.addColumn({type: 'string', role:'annotation'});
            currentRouteIndex = dataFilterByTime(time);
            drawChart(currentRouteIndex);
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

        // Flip tags
        $('#chart').click(function(){
            $('.map-section').removeClass('active');
            $('.replay-controls-wrapper').show();
            $('.profile-section').removeClass('active');
            $('.elevation-section').addClass('active');
            resizeChart ();
        })
        $('#profile-tab').click(function(){
            $('.elevation-section').removeClass('active');
            $('.replay-controls-wrapper').hide();
            $('.map-section').removeClass('active');
            $('.profile-section').addClass('active');
        })
        $('#home-tab').click(function(){
            $('.map-section').addClass('active');
            $('.replay-controls-wrapper').show();
            $('.elevation-section').removeClass('active');
            $('.profile-section').removeClass('active');
            initMap();
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

            // create array
            var array = [];
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
    })
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
