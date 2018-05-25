@extends('app')

@section('htmlheader_title')
    Live Tracking
@endsection

@section('contentheader_title')
    Live Tracking
@endsection

@section('main-content')
<div class="container-flex">

    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li id="home-tab" class="active"><a href="#" data-toggle="tab">Map</a></li>
            <li id="chart" ><a href="#" data-toggle="tab">Elevation</a></li>
            <li id="profile-tab" ><a href="#" data-toggle="tab">Athletes</a></li>
        </ul>
        <div class="tab-content">
            <div class="map-section tab-pane active">
                <div class="form-group" style="color: #666; float: left;">Athletes' latest locations from <b>{{$event->datetime_from}}</b> to <b>{{$event->datetime_to}}</b></div>
                <div style="color: #666; float: right;">Elapsed Time: <b><span id="time"></span></b></div>
                <div id="map"></div> {{-- google map here --}}
            </div>
            <div class="elevation-section tab-pane" id="elevationChart" style="width:100%; height:100%;"></div>
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
        var elevations_global;

        function initMap() {

            data = {!! $data !!};
            // console.log(data);

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
                            html += '<div>Country: <b>' + content['country'] + '</b></div>';
                            html += '<div>Device ID: <b>' + content['device_id'] + '</b></div>';

                            if ( marker.profile ) { // update
                                html += '<div>Location: <b>' + parseFloat(marker.profile['latitude_final']).toFixed(6) + ', ' + parseFloat(marker.profile['longitude_final']).toFixed(6) + '</b></div>';
                                html += '<div>Distance: <b>' + marker.profile['distance'].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' m' + '</b></div>';
                            } else{ // initialize
                                html += '<div>Location: <b>' + parseFloat(location['lat']).toFixed(6) + ', ' + parseFloat(location['lng']).toFixed(6) + '</b></div>';
                                html += '<div>Distance: <b>' + content['distance'].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' m' + '</b></div>';
                            }

                            if ( marker.checkpointData ){ // update
                                html += '<hr style="margin-top: 8px; margin-bottom: 8px;">';
                                for (var i = 0; i < marker.checkpointData.length; i++) {
                                    html += '<div>Checkpoint '  + marker.checkpointData[i]['checkpoint'] + ': <b>'+ marker.checkpointData[i]['reached_at'] + '</b></div>';
                                }
                            }else{ // initialize
                                if (checkpointData[content['device_id']]) {
                                    html += '<hr style="margin-top: 8px; margin-bottom: 8px;">';
                                    // show athlete reaches at checkpoint time
                                    var checkpointTimes = checkpointData[content['device_id']];
                                    for (var j = 0; j < checkpointTimes.length; j++) {
                                        html += '<div>Checkpoint ' + checkpointTimes[j]['checkpoint'] + ': <b>'+ checkpointTimes[j]['reached_at'] + '</b></div>';
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

                var map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 13,
                    // center: {lat: 22.404767, lng: 114.1057550}
                    center: {lat: 22.3016616, lng: 114.1577151}
                });

                // set style
                map.set('styles', mapStyle);

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
                    tempmarkers = [];
                    var CPIndex = [];
                    for(var key in route){
                        gpxLat = parseFloat(route[key]["lat"]);
                        gpxLng = parseFloat(route[key]["lon"]);
                        IsCP = route[key]["isCheckpoint"];
                        if(IsCP){
                            CPIndex.push(key);
                        }
                        addLatLngInit(new google.maps.LatLng(gpxLat, gpxLng));
                    }
                    // console.log(CPIndex);

                    // start point and end point marker
                    // console.log(tempmarkers);
                    // console.log(tempmarkers[0].position);
                    // console.log(tempmarkers[tempmarkers.length - 1].position);
                    var startPointMarker = new google.maps.Marker({
                        position: tempmarkers[0].position,
                        label: {text: "Start", color: "white", fontSize: "10px"},
                        map: map
                    });

                    var endPointMarker = new google.maps.Marker({
                        position: tempmarkers[tempmarkers.length - 1].position,
                        label: {text: "Fin.", color: "white", fontSize: "10px"},
                        map: map,
                        // icon: '{{url('/')}}/racing-flag.png',
                    });

                    for (var i = 0; i < CPIndex.length; i++) {
                        var index = CPIndex[i];
                        var checkpointMarker = new google.maps.Marker({
                            position: tempmarkers[index].position,
                            label: {text: ""+(i+1)+"", color: "white"},
                            map: map
                        });
                    }

                    var bounds = new google.maps.LatLngBounds();
                    for (var i = 0; i < tempmarkers.length; i++) {
                        bounds.extend(tempmarkers[i].getPosition());
                    }
                    map.fitBounds(bounds);

                @endif

                // set InfoWindow pixelOffset
                infowindow = new google.maps.InfoWindow({
                    pixelOffset: new google.maps.Size(0, -36),
                });

                // Add Markers
                var markers = [];
                // check device_id in localStorage
                var temp = localStorage.getItem("visibility{{$event_id}}");
                var array = jQuery.parseJSON( temp );
                // console.log(" array: " + array );

                for (var i = 0; i < data.length; i++) {
                    var location = {lat: parseFloat(data[i]['latitude_final']), lng: parseFloat(data[i]['longitude_final'])};

                    // console.log(data[i]['device_id']);
                    // localStorage is not empty
                    if (temp !== null) {
                        if (jQuery.inArray(data[i]['device_id'], array) !== -1) {
                            markers[data[i]['device_id']] = (addMarker(location, map, data[i]));
                        }
                    } else {
                        if (data[i]['status'] == "visible"){
                            markers[data[i]['device_id']] = (addMarker(location, map, data[i]));
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

            setInterval(function()
            {
                $.ajax({
                    type:"get",
                    url:"{{url('/')}}/event/{{$event_id}}/live-tracking/poll",
                    dataType:"json",
                    success:function(data)
                    {
                        var array = data['data'];
                        var checkpointData = data['checkpointData'];
                        var currentRouteIndex = data['currentRouteIndex'];
                        drawChart(currentRouteIndex);

                        // console.log(currentRouteIndex);
                        for (var key in array) {
                            console.log(array);
                            if (markers[array[key]['device_id']]) {
                                markers[array[key]['device_id']].setPosition( new google.maps.LatLng(parseFloat(array[key]['latitude_final']), parseFloat(array[key]['longitude_final'])) );
                                markers[array[key]['device_id']].profile = array[key];
                                markers[array[key]['device_id']].checkpointData = checkpointData[array[key]['device_id']];
                                // console.log(markers[array[key]['device_id']]);

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


        // Takes an array of ElevationResult objects, draws the path on the map
        // and plots the elevation profile on a Visualization API ColumnChart.
        function plotElevation(elevations, status) {
            elevations_global = elevations;
            var chartDiv = document.getElementById('elevationChart');
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
            // console.log(currentRouteIndex);

            var chartHeight = $(window).height() * .8;

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
               hAxis: { showTextEvery: 64,
               slantedText:true, slantedTextAngle:45},
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
            elevationData.addColumn({type: 'string', role:'tooltip', p: {html: true}});
            elevationData.addColumn({type: 'string', role:'annotation'});
            elevationData.addColumn({type: 'string', role:'annotationText', p: {html: true}});

            for (var i = 0; i < elevations_global.length; i++) {
                // the current athlete's distance between
                var dist = distance/elevations_global.length * i;
                var nextDist = distance/elevations_global.length * (i+1);

                var annotationStr = "";
                var athleteCount = 0;
                var str = "";
                str += 'Distance: <b>' + String((distance/elevations_global.length * i).toFixed(0)) + ' m</b><br/>Elevation: <b>' + elevations_global[i].elevation.toFixed(0) + ' m</b><hr class="end" style="width: 100%; positive: absolute; margin-left: 0;">';
                for (var j = 0; j < currentRouteIndex.length; j++) {
                    var athletheDist = currentRouteIndex[j]['distance'];
                    var athletheDeviceID = currentRouteIndex[j]['device_id'];
                    var athleteBibNumber = currentRouteIndex[j]['bib_number'];
                    var athleteFirstName = currentRouteIndex[j]['first_name'];
                    var athleteLastName = currentRouteIndex[j]['last_name'];
                    var athleteChineseName = currentRouteIndex[j]['zh_full_name'];

                    if (dist <= athletheDist && athletheDist < nextDist){
                        str += 'Bib Number: <b>' + athleteBibNumber + '</b><br/>';
                        str += 'First Name: <b>' + athleteFirstName + '</b><br/>';
                        str += 'Last Name: <b>' + athleteLastName + '</b><br/><hr class="end" style="width: 100%; positive: absolute; margin-left: 0;">';

                        athleteCount++;
                        if (athleteCount == 1) {
                            annotationStr = athleteBibNumber;
                        } else {
                            annotationStr = '(' + athleteCount + ')';
                        }
                    }

                }
                // strDist = strDist.slice(0, -1);

                if (annotationStr.length>0){
                    elevationData.addRow([parseInt(distance/elevations_global.length * i), elevations_global[i].elevation, '<div class="chart-info-window">Distance: <b>'+String((distance/elevations_global.length * i).toFixed(0))+'m</b><br/>Elevation:<b>'+elevations_global[i].elevation.toFixed(0)+' m</b></div>', annotationStr, '<div class="chart-info-window">'+str+'</div>']);
                } else {
                    elevationData.addRow([parseInt(distance/elevations_global.length * i), elevations_global[i].elevation, '<div class="chart-info-window">Distance: <b>'+String((distance/elevations_global.length * i).toFixed(0))+' m</b><br/>Elevation: <b>'+elevations_global[i].elevation.toFixed(0)+' m</b></div>', null, null]);
                }
            }

            var chartHeight = $(window).height() * .8;

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
                    gridlines: {
                        color: 'transparent'
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

            chart.draw(elevationData, chartOptions);
        }

        initMap();

        function addLatLngInit(position) {

            path = poly.getPath();

            // Because path is an MVCArray, we can simply append a new coordinate
            // and it will automatically appear.
            path.push(position);

            // Add a new marker at the new plotted point on the polyline.
            var marker = new google.maps.Marker({
                position: position,
                title: '#' + path.getLength(),
                map: map
            });

            tempmarkers.push(marker);

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


        $('#chart').click(function(){
            $('.map-section').removeClass('active');
            $('.profile-section').removeClass('active');
            $('.elevation-section').addClass('active');
            resizeChart ();
        })
        $('#profile-tab').click(function(){
            $('.elevation-section').removeClass('active');

            $('.map-section').removeClass('active');
            $('.profile-section').addClass('active');
        })
        $('#home-tab').click(function(){
            $('.map-section').addClass('active');
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

        // ios botton
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


    </script>

@endsection
