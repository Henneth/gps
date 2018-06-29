@extends('app')

@section('htmlheader_title')
    Draw Route
@endsection

@section('contentheader_title')
    Draw Route
@endsection

@section('css')
    <style>
        #map {
            height:80vh;
            width: 100%;
        }

        .switchBtn {
            display: inline-block;
            float: right;
        }
    </style>
@endsection

@section('main-content')
    @include('partials/alerts')
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li id="map-tab" class="active"><a href="#tab_1" data-toggle="tab">Draw Route</a></li>
        @if ($event_type && $event_type->event_type != "shortest route")
            <li id="min-time-tab"><a href="#tab_2" data-toggle="tab">Set Minimum Times</a></li>
            <li id="set-checkpoint-name-tab"><a href="#tab_3" data-toggle="tab">Set Checkpoint Name</a></li>
        @endif
        </ul>
        <div class="tab-content">
            <div class="map-section tab-pane active" id="tab_1">
                <div class="form-group">
            		<button type="button" class="btn btn-default" id="undo" style="margin-right:4px;"><i class="fa fa-undo"></i> Undo</button>
            		<div class="data-form display-inline-block">
            			<form id="save-route" action="{{ url('/') }}/event/{{$event_id}}/save-route" method="POST">
            				{{ csrf_field() }}
            				<input type="hidden" id="route" name="route">
            				<button type="sumbit" class="btn btn-primary" id="save"><i class="far fa-save"></i> Save Route</button>
            			</form>
            		</div>{{--
                    <div class="switchBtn">
                        <label>Use GPX</label>
                        <input class="tgl tgl-ios" id="cb1" type="checkbox"/>
                        <label class="tgl-btn" for="cb1"></label>
                    </div> --}}
                    <div class="pull-right">
                        <button class="btn btn-primary" onclick="toggleExcelImport();return false;"><i class="fas fa-upload"></i>&nbsp; Import GPX File</button>
                    </div>
            	</div>

                <div id="excelImportBox" class="box box-primary" style="display: none;">
                    <div class="box-header with-border">
                        <h3 class="box-title">Import GPX File</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    <form role="form" action="{{url('/')}}/event/{{$event_id}}/edit-event/gpx-file-upload" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="box-body">
                            <div class="form-group">
                                <label for="excelFile">GPX file upload</label>
                                <input type="file" id="excelFile" name="fileToUpload">

                                <p class="help-block">.gpx file only.</p>
                            </div>
                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            	<div class="container-flex">
            	    <div id="map"></div>
            	</div>
            </div>
        @if ($event_type && $event_type->event_type != "shortest route")
            <div class="min-time-section tab-pane" id="tab_2">
                @if ($checkpointMinTimes && $checkpointMinTimes[sizeof($checkpointMinTimes)-1]->checkpoint != 0)
                <form action="{{url('/')}}/event/{{$event_id}}/save-minimum-times" method="post">
                    {{ csrf_field() }}
                    @for ($i=0; $i < sizeof($checkpointMinTimes); $i++)
                        <div class="form-group min-times-row">
                            <label>From {{($i == 0) ? 'Start' : 'Checkpoint'.($checkpointMinTimes[$i]->checkpoint - 1)}} to {{($checkpointMinTimes[$i]->checkpoint == sizeof($checkpointMinTimes)) ? 'Finish' : 'Checkpoint'.($checkpointMinTimes[$i]->checkpoint)}}</label>
                            <input type="text" class="form-control" placeholder="Minimum time (HH:MM:SS)" autocomplete="off" name="min_times[{{$checkpointMinTimes[$i]->route_distance_id}}]" value="{{$checkpointMinTimes[$i]->min_time}}">
                        </div>
                    @endfor
                    <div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
                @else
                <div>No checkpoints yet. Please draw the route first.</div>
                @endif
            </div>
            <div class="set-checkpoint-name tab-pane" id="tab_3">
                @if ($checkpointMinTimes && $checkpointMinTimes[sizeof($checkpointMinTimes)-1]->checkpoint != 0)
                <form action="{{url('/')}}/event/{{$event_id}}/save-checkpoint-name" method="post">
                    {{ csrf_field() }}
                    @for ($i=0; $i < sizeof($checkpointMinTimes)-1; $i++)
                        <div class="form-group">
                            <label>Name of Checkpoint {{($checkpointMinTimes[$i]->checkpoint)}}</label>
                            <input type="text" class="form-control" placeholder="Name of Checkpoint" autocomplete="off" name="checkpoint_name[{{$checkpointMinTimes[$i]->route_distance_id}}]" value="{{$checkpointMinTimes[$i]->checkpoint_name}}">
                        </div>
                    @endfor
                    <div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
                @else
                <div>No checkpoints yet. Please draw the route first.</div>
                @endif
            </div>
            </div>
        @endif
        </div>
    </div>
@endsection

@section('js')
    <script>
    var poly;
    var map;
    var polyIndex = 0;

    //Mirrors the path array to find index to delete and such
    var mirrorCoordinates = [];

    var encodeString;
    var path;
    var gpxLat;
    var gpxLng;
    var IsCP;

    // set info window
    var marker;
    var uniqueId = 1;
    var infoWindow;
    var markerList = []; //array to store marker

    function initMap() {

        infoWindow = new google.maps.InfoWindow();
        // var myLatlng = new google.maps.LatLng(22.3016616, 114.1577151);
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 12,
            center: {lat: 22.3016616, lng: 114.1577151}  // Center the map on Hong Kong.
        });

        poly = new google.maps.Polyline({
            strokeColor: '#3d00f7',
            strokeOpacity: 1,
            strokeWeight: 2
        });
        poly.setMap(map);
        polyIndex = 0;
        //Mirrors the path array to find index to delete and such
        mirrorCoordinates = [];
        @if($data)
            var data = {!!$data->route!!};
            // console.log(data);
            for(var key in data){
                gpxLat = parseFloat(data[key]["lat"]);
                gpxLng = parseFloat(data[key]["lon"]);
                IsCP = data[key]["isCheckpoint"];
                addLatLngInit(IsCP, new google.maps.LatLng(gpxLat, gpxLng));
            }

            reOrder();

            var bounds = new google.maps.LatLngBounds();
            for (var i = 0; i < markerList.length; i++) {
                bounds.extend(markerList[i].getPosition());
            }
            map.fitBounds(bounds);

        @endif

        map.addListener('click', addLatLng);
    }

    // Handles click events on a map, and adds a new point to the Polyline.
    function addLatLng(event) {
        path = poly.getPath();

        // Because path is an MVCArray, we can simply append a new coordinate
        // and it will automatically appear.
        path.push(event.latLng);

        mirrorCoordinates.push(event.latLng);
        polyIndex++;

        // Add a new marker at the new plotted point on the polyline.
        var marker = new google.maps.Marker({
            position: event.latLng,
            title: '#' + path.getLength(),
            map: map,
            isCheckpoint: 0
        });

        //Set unique id
        marker.id = uniqueId;
        uniqueId++;

        showInfoWindow(marker);

        reOrder();
    }

    // Handles click events on a map, and adds a new point to the Polyline.
    function addLatLngInit(IsCP, position) {

        path = poly.getPath();

        // Because path is an MVCArray, we can simply append a new coordinate
        // and it will automatically appear.
        path.push(position);

        mirrorCoordinates.push(position);
        polyIndex++;

        // Add a new marker at the new plotted point on the polyline.
        var marker = new google.maps.Marker({
            position: position,
            title: '#' + path.getLength(),
            map: map,
            isCheckpoint: IsCP,
            // resize icon https://developers.google.com/maps/documentation/javascript/markers
        });

        //Set unique id
        marker.id = uniqueId;
        uniqueId++;

        showInfoWindow(marker);
    }

    var event_type = '{{$event_type->event_type}}'; // get event_type from DB

    function showInfoWindow(marker){
        //Attach click event handler to the marker.
        google.maps.event.addListener(marker, "click", function (e) {
            var content = 'Location: ' + parseFloat(marker.internalPosition.lat()).toFixed(6) + ', ' + parseFloat(marker.internalPosition.lng()).toFixed(6);

            if (!marker.isStartEnd && event_type == "fixed route") {
                if (marker.isCheckpoint == 1){
                    content += "<br /><b> Remove this checkpoint? </b>";
                    content += "<br /><input type = 'button' onclick = 'removeCheckpoint(" + marker.id + ");' value = 'Confirm' />";
                }else {
                    content += "<br /><b> Set it as checkpoint? </b>";
                    content += "<br /><input type = 'button' onclick = 'setAsCheckpoint(" + marker.id + ");' value = 'Confirm' />";
                }
            }

            infoWindow.setContent(content);
            infoWindow.open(map, this);
        });
        // google.maps.event.trigger(marker, 'click');
        markerList.push(marker);
    }
    // google.maps.event.addDomListener(window, "load", initialize);
    function setAsCheckpoint(id){
        for (var i = 0; i < markerList.length; i++) {
            if ( markerList[i].id == id ){
                infoWindow.setContent('<div style="color: green">' + infoWindow.getContent() + "</div>");
                markerList[i]['isCheckpoint'] = 1;
                markerList[i].setIcon(null);
                infoWindow.close(map, this);
            }
            // console.log(markerList[i]);
        }
        reOrder();
    }

    function removeCheckpoint(id){
        for (var i = 0; i < markerList.length; i++) {
            if ( markerList[i].id == id ){
                infoWindow.setContent('<div style="color: red">' + infoWindow.getContent() + "</div>");
                markerList[i]['isCheckpoint'] = 0;
                markerList[i].setIcon('{{ url('/') }}/img/icons/triangle.png');
                infoWindow.close(map, this);
            }
            // console.log(markerList[i]);
        }
        reOrder();
    }

    function reOrder(){
        var CPIndex = 1;
        for (var i = 0; i < markerList.length; i++) {
            markerList[i].setLabel(null);
            markerList[i].isStartEnd = false;
            if (markerList[i].isCheckpoint){
                markerList[i].setLabel({text: ""+CPIndex, color: "white"});
                CPIndex++;
            } else {
                markerList[i].setIcon('{{ url('/') }}/img/icons/triangle.png');
            }
        }
        if ( markerList[markerList.length-1] ){
            markerList[markerList.length-1].setLabel({text: "Fin.", color: "white", fontSize: "10px"});
            markerList[markerList.length-1].isStartEnd = true;
            markerList[markerList.length-1].setIcon(null);
        }
        if ( markerList[0] ){
            markerList[0].setLabel({text: "Start", color: "white", fontSize: "10px"});
            markerList[0].isStartEnd = true;
            markerList[0].setIcon(null);
            markerList[0].isCheckpoint = 0;
        }
    }

    // function useGPX(){
    //         // var myLatlng = new google.maps.LatLng(22.3016616, 114.1577151);
    //         map = new google.maps.Map(document.getElementById('map'), {
    //             zoom: 12,
    //             center: {lat: 22.3016616, lng: 114.1577151}  // Center the map on Hong Kong.
    //         });


    //         poly = new google.maps.Polyline({
    //             strokeColor: '#3d00f7',
    //             strokeOpacity: 1,
    //             strokeWeight: 3
    //         });
    //         poly.setMap(map);


    //         for(var key in gpxData){
    //             gpxLat = parseFloat(gpxData[key]["latitude"]);
    //             gpxLng = parseFloat(gpxData[key]["longitude"]);
    //             // test.push(new google.maps.LatLng(gpxLat, gpxLng));
    //             // test.push = ({lat: gpxLat, lng:gpxLng});
    //             addLatLngInit(new google.maps.LatLng(gpxLat, gpxLng));

    //         }


    //         var bounds = new google.maps.LatLngBounds();
    //         for (var i = 0; i < markers.length; i++) {
    //             bounds.extend(markers[i].getPosition());
    //         }
    //         map.fitBounds(bounds);

    //         map.addListener('click', addLatLng);
    // }

    // $('.tgl-btn').click(function(){
    //     // useGPX();

    //     $('#undo').hide();
    //     $('#save').hide();
    // });
    // console.log(gpxData);
    //Remove the Polyline and the previous marker
    $('#undo').click(function(){
        if(markerList && markerList.length){
            mirrorCoordinates.pop();
            polyIndex--;
            poly.getPath().setAt(polyIndex, mirrorCoordinates[mirrorCoordinates.length - 1]);
            poly.getPath().removeAt(polyIndex);

            // console.log(markers.length);

            var markerIndex = markerList.length - 1;
            markerList[markerIndex].setMap(null);
            markerList.pop();
            reOrder();
        }
    });

    $('#save').click(function(e){
        e.preventDefault();
        var array = [];
        for (var i = 0; i < markerList.length; i++) {
            markerList[i];
            if (i == markerList.length - 1) {
                var temp = {'lat': markerList[i].position.lat(), 'lon': markerList[i].position.lng(), 'isCheckpoint': 1};
                array.push(temp);
            } else {
                var temp = {'lat': markerList[i].position.lat(), 'lon': markerList[i].position.lng(), 'isCheckpoint': markerList[i].isCheckpoint};
                array.push(temp);
            }
        }

        console.log(array);
        console.log(markerList);

        encodeString = JSON.stringify(array);
        console.log(encodeString);

        // encodeString = google.maps.geometry.encoding.encodePath(path);
        $('#route').val(encodeString);
        // console.log(path.b[0].lat());
        // Set save-route value
        document.getElementById('save-route').submit();
    });

    // gpx file upload btn
    function toggleExcelImport() {
        $('#excelImportBox').toggle();
    }
    </script>

    <!-- Google Maps -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4i5s_R4E6Y8c5m4pEVxeVQvCJorm4MaI&libraries=geometry&callback=initMap"></script>
@endsection
