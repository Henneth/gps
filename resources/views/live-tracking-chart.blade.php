@extends('app')

@section('htmlheader_title')
    Live Tracking
@endsection

@section('contentheader_title')
    Live Tracking <small>{{$event->event_id == current_event ? $event->event_name : '' }}</small>
@endsection

@section('main-content')
    <div class="container-flex">
        <div class="loading" id="loading" style="display: none;">
            <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
        </div>
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li id="home-tab" <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 0 ? 'class="active"' : '');} else{echo 'class="active"';} ?> ><a href="#" data-toggle="tab">Map</a></li>
                @if ($event->event_type == "fixed route")
                    <li id="chart" <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 1 ? 'class="active"' : '');} else{} ?> ><a href="#" data-toggle="tab">Elevation</a></li>
                @endif
                <li id="profile-tab" <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 2 ? 'class="active"' : '');} else{} ?> ><a href="#" data-toggle="tab">Athletes</a></li>
            </ul>
            <div class="tab-content">
                @if ($event->event_type == "fixed route")
                    <div class="elevation-section tab-pane <?php if (isset($_GET['tab'])) {echo ($_GET['tab'] == 1 ? 'active' : '');} else{} ?>" id="elevationChart" style="width:100%; height:100%;"></div>
                @endif
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

        .lds-ellipsis {
          display: inline-block;
          position: relative;
          width: 64px;
          height: 64px;
        }
        .lds-ellipsis div {
          position: absolute;
          top: 27px;
          width: 11px;
          height: 11px;
          border-radius: 50%;
          background: #fff;
          animation-timing-function: cubic-bezier(0, 1, 1, 0);
        }
        .lds-ellipsis div:nth-child(1) {
          left: 6px;
          animation: lds-ellipsis1 0.6s infinite;
        }
        .lds-ellipsis div:nth-child(2) {
          left: 6px;
          animation: lds-ellipsis2 0.6s infinite;
        }
        .lds-ellipsis div:nth-child(3) {
          left: 26px;
          animation: lds-ellipsis2 0.6s infinite;
        }
        .lds-ellipsis div:nth-child(4) {
          left: 45px;
          animation: lds-ellipsis3 0.6s infinite;
        }
        @keyframes lds-ellipsis1 {
          0% {
            transform: scale(0);
          }
          100% {
            transform: scale(1);
          }
        }
        @keyframes lds-ellipsis3 {
          0% {
            transform: scale(1);
          }
          100% {
            transform: scale(0);
          }
        }
        @keyframes lds-ellipsis2 {
          0% {
            transform: translate(0, 0);
          }
          100% {
            transform: translate(19px, 0);
          }
        }

        .loading {
            position: fixed;
            z-index: 10000;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
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

        @if ($event->event_type == "fixed route")
            // elevation chart
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(initChart);
        @endif

        var chart;
        var chartOptions;
        var elevationData;
        var distance = 0;
        var infowindow2;
        var elevations_global;
        var currentRouteIndex;
        var checkpointDistances;
        var markerList = []; //array to store marker
        var firstLoad = true;
        var showOffKey; // store "ON" device_id, data retrive from localStorage
        var data;
        var route;

        checkpointDistances = {!! $checkpointDistances !!};

        @if ($route)
            route = {!!$route->route!!};
        @endif

        // check device_id in localStorage, "ON" data will be save in localStorage
        var temp = localStorage.getItem("visibility{{$event_id}}");
        var array = jQuery.parseJSON( temp );
        showOffKey = array;

        function pollData(firstTime = false) {
            $.ajax({
                type:"get",
                url:"{{url('/')}}/event/{{$event_id}}/live-tracking/poll",
                data: {'device_ids': showOffKey ? JSON.stringify(showOffKey) : null},
                dataType:"json",
                success:function(ajax_data) {
                    if (firstTime) {
                        $('#loading').fadeOut('slow',function(){$(this).remove();});
                    }
                    data = ajax_data;
                    console.log('polling...');

                    currentRouteIndex = lastPositionData();
                    if (elevations_global) {
                        drawChart(currentRouteIndex);
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
        setInterval(pollData, 10000);//time in milliseconds

        function initChart() {

            // Create an ElevationService.
            var elevator = new google.maps.ElevationService;

            // Draw the path, using the Visualization API and the Elevation service.
            displayPathElevation(elevator);
        }

        // draw elevation chart initializtion
        function displayPathElevation(elevator) {
            var tempArray = [];

            for(var key in route){
                gpxLat = parseFloat(route[key]["lat"]);
                gpxLng = parseFloat(route[key]["lon"]);
                tempArray.push({'lat' : gpxLat, 'lng' : gpxLng});
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
            currentRouteIndex = lastPositionData();
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

                for (var j in currentRouteIndex) {
                    if(currentRouteIndex[j]['distance']) {
                        var athleteDist = currentRouteIndex[j]['distance']['distance'];
                        var athleteBibNumber = currentRouteIndex[j]['athlete']['bib_number'];
                        var athleteFirstName = currentRouteIndex[j]['athlete']['first_name'];
                        var athleteLastName = currentRouteIndex[j]['athlete']['last_name'];
                        var athleteChineseName = currentRouteIndex[j]['athlete']['zh_full_name'];
                        var athleteColour = currentRouteIndex[j]['athlete']['colour_code'];
                        // console.log(distance);

                        if (dist <= athleteDist && athleteDist < nextDist){
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

        // get last position of athlete
        function lastPositionData() {
            var athleteArray = [];

            if(typeof data !== 'undefined' && data){
                for (var device_id in data) {
                    if( typeof data[device_id] !== 'undefined' && data[device_id] ){
                        var routeIndexByDevice = data[device_id]['distances'];

                        athleteArray[device_id] = {
                            'distance': routeIndexByDevice[routeIndexByDevice.length-1],
                            'athlete': data[device_id]['athlete']
                        };
                    }
                }
            }
            return athleteArray;
        };

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

    </script>

@endsection
