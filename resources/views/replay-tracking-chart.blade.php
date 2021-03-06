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
                        <div style="block" class="time-board-a">
                            <span>{{$event->datetime_from}}</span>
                            <span style="float:right;">{{$event->datetime_to}}</span>
                        </div>
                        <div style="block" class="time-board-b">
                            <span style="float:left;">
                                <?php $splitDate = explode(' ', $event->datetime_from);?>
                                {{$splitDate[0]}}<br>{{$splitDate[1]}}
                            </span>
                            <span style="float:right;">
                                <?php $splitDate = explode(' ', $event->datetime_to);?>
                                {{$splitDate[0]}}<br>{{$splitDate[1]}}
                            </span>
                        </div>
                    </div>
                </div>
                @if($event->event_type =='fixed route')
                    <div class="elevation-section tab-pane active">
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
        @if($event->event_type =='fixed route')
            // elevation chart
            google.charts.load('current', {'packages':['corechart']});
            google.charts.setOnLoadCallback(initChart);
        @endif

        // init global variables
        var chart;
        var chartOptions;
        var elevationData;
        var distance = 0;
        var elevations_global;
        var currentRouteIndex;
        var checkpointData;
        var localStorageArray; // store "ON" bib_number, data retrive from localStorage
        var data;
        var route;

        checkpointData = {!! $checkpoint !!};

        function initial() {

            @if ($route)
                route = {!!$route!!};
            @endif

            // check bib_number in localStorage, "ON" data will be saved in localStorage
            var temp = localStorage.getItem("visibility{{$event_id}}");
            var array = jQuery.parseJSON( temp );
            localStorageArray = array;
            // console.log(localStorageArray);

            $('#loading').show();
            $.ajax({
                type:'get',
                url:'{{url("/")}}/event/{{$event_id}}/replay-tracking/poll',
                data: {'bib_numbers': localStorageArray ? JSON.stringify(localStorageArray) : null},
                dataType: "json",
                success:function(ajax_data) {
                    $('#loading').fadeOut('slow',function(){$(this).remove();});
                    data = ajax_data;
                    console.log('polling...');

                    currentRouteIndex = lastPositionData();
                    if (elevations_global) {
                        drawChart(currentRouteIndex);
                    }
                    console.log(data);
                },
                error:function() {
                    $('#loading').fadeOut('slow',function(){$(this).remove();});
                }
            });

        }



        function initChart() {
            // Create an ElevationService.
            var elevator = new google.maps.ElevationService;
            // Draw the path, using the Visualization API and the Elevation service.
            displayPathElevation(elevator);
        }

        function displayPathElevation(elevator) {
            var tempArray = [];
            for(var key in route){
                gpxLat = parseFloat(route[key]["latitude"]);
                gpxLng = parseFloat(route[key]["longitude"]);
                tempArray.push({'lat' : gpxLat, 'lng' : gpxLng});
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
            //     var step = Math.pow(10,Math.floor(distance).toString().length-1);
            // } else {
            //     var step = Math.pow(10,Math.floor(distance).toString().length-2)*tempNo;
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
                        var athleteDist = currentRouteIndex[j]['distance']['distance_from_start'];
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
                var count = 0;
                // console.log(checkpointData);
                for (var key in checkpointData) {
                    if( checkpointData[key]['display'] == 1 ){
                        if (dist <= checkpointData[key]['distance_from_start'] && checkpointData[key]['distance_from_start'] < nextDist){
                            if (checkpointData[key]['checkpoint_name']) {
                                var checkpoint = String(checkpointData[key]['checkpoint_name']);
                            }else {
                                if (key == checkpointData.length -1) {
                                    var checkpoint = String('Finish');
                                } else {
                                    if(key != 0){
                                        var checkpoint = String('CP' + count);
                                    }
                                }
                            }
                            break;
                        }
                        count++;
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

        //On button click, load new data
        function dataFilterByTime(datetime) {
            var athleteArray = [];

            for (var bib_number in data) {
                var routeIndexByBibNum = data[bib_number]['distances'];
                for (var j = 0; j < routeIndexByBibNum.length; j++) {
                    getTimeByBibNum = routeIndexByBibNum[j]['datetime'];
                    getTimeByBibNum = new Date(getTimeByBibNum).getTime() / 1000;
                    if (datetime >= getTimeByBibNum){
                        athleteArray[bib_number] = {
                            'distance': routeIndexByBibNum[j],
                            'athlete': data[bib_number]['athlete']
                        };
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

                        athleteArray[bib_number] = {
                            'distance': routeIndexByBibNum[routeIndexByBibNum.length-1],
                            'athlete': data[bib_number]['athlete']
                        };
                    }
                }
            }
            return athleteArray;
        };


        initial();

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

                var offset = (timestamp_to - timestamp_from) * pc / 100;
                var time = offset + timestamp_from;

                var dateString = moment.unix(time).format("YYYY-MM-DD HH:mm:ss");

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

            var url_string = window.location.href; //window.location.href
            var url = new URL(url_string);

            // Flip tags
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

        })
    </script>
@endsection

@section('css')
    <style>
    #map {
        height:80vh;
        width: 100%;
    }

    .replay-controls {
        max-height: 48px;
    }

    .time-board-a {
        display: block;
    }
    .time-board-b {
        display: none;
    }

    /* Extra small devices (portrait phones, less than 576px) */
    @media (max-width: 575.98px) {
        .replay-controls-wrapper {
            display: block !important;
        }
        .time-board-a {
            display: none;
        }
        .time-board-b {
            display: block;
            height: 20px;
        }
        .slider-wrapper{
            margin-top: 8px;
        }
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
