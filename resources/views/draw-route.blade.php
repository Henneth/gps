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

.tgl {
  display: none;
}
.tgl, .tgl:after, .tgl:before, .tgl *, .tgl *:after, .tgl *:before, .tgl + .tgl-btn {
  box-sizing: border-box;
}
.tgl::-moz-selection, .tgl:after::-moz-selection, .tgl:before::-moz-selection, .tgl *::-moz-selection, .tgl *:after::-moz-selection, .tgl *:before::-moz-selection, .tgl + .tgl-btn::-moz-selection {
  background: none;
}
.tgl::selection, .tgl:after::selection, .tgl:before::selection, .tgl *::selection, .tgl *:after::selection, .tgl *:before::selection, .tgl + .tgl-btn::selection {
  background: none;
}
.tgl + .tgl-btn {
  outline: 0;
  display: block;
  width: 4em;
  height: 2em;
  position: relative;
  cursor: pointer;
  -webkit-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
          user-select: none;
}
.tgl + .tgl-btn:after, .tgl + .tgl-btn:before {
  position: relative;
  display: block;
  content: "";
  width: 50%;
  height: 100%;
}
.tgl + .tgl-btn:after {
  left: 0;
}
.tgl + .tgl-btn:before {
  display: none;
}
.tgl:checked + .tgl-btn:after {
  left: 50%;
}


.tgl-ios + .tgl-btn {
  background: #fbfbfb;
  border-radius: 2em;
  padding: 2px;
  transition: all .4s ease;
  border: 1px solid #e8eae9;
}
.tgl-ios + .tgl-btn:after {
  border-radius: 2em;
  background: #fbfbfb;
  transition: left 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), padding 0.3s ease, margin 0.3s ease;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1), 0 4px 0 rgba(0, 0, 0, 0.08);
}
.tgl-ios + .tgl-btn:hover:after {
  will-change: padding;
}
.tgl-ios + .tgl-btn:active {
  box-shadow: inset 0 0 0 2em #e8eae9;
}
.tgl-ios + .tgl-btn:active:after {
  padding-right: .8em;
}
.tgl-ios:checked + .tgl-btn {
  background: #86d993;
}
.tgl-ios:checked + .tgl-btn:active {
  box-shadow: none;
}
.tgl-ios:checked + .tgl-btn:active:after {
  margin-left: -.8em;
}
.switchBtn {
    display: inline-block; 
    float: right;
}

    </style>
@endsection

@section('main-content')
    @include('partials/alerts')
	<div class="form-group">
		<button type="button" class="btn btn-default" id="undo" style="margin-right:4px;"><i class="fa fa-undo"></i> Undo</button>
		<div class="data-form display-inline-block">
			<form id="save-route" action="{{ url('/') }}/event/{{$event_id}}/save-route" method="POST">
				{{ csrf_field() }}
				<input type="hidden" id="route" name="route">
				<button type="sumbit" class="btn btn-primary" id="save"><i class="far fa-save"></i> Save Route</button>
			</form>
		</div>
        <div class="switchBtn">
            <label>Use GPX</label>
            <input class="tgl tgl-ios" id="cb1" type="checkbox"/>
            <label class="tgl-btn" for="cb1"></label>
        </div>
	</div>
	<div class="container-flex">
	    <div id="map"></div>
	</div>
@endsection

@section('js')
    <script>
    var poly;
    var map;
    var polyIndex = 0;
    //Mirrors the path array to find index to delete and such
    var mirrorCoordinates = [];
    var markers = [];
    var encodeString;
    var path;
    var gpxLat;
    var gpxLng;

    @if(!$data->route)
        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: {lat: 22.3016616, lng: 114.1577151}  // Center the map on Chicago, USA.
            });

            poly = new google.maps.Polyline({
                strokeColor: '#3d00f7',
                strokeOpacity: 1,
                strokeWeight: 3
            });
            poly.setMap(map);

            // Add a listener for the click event
            map.addListener('click', addLatLng);
        }

    @else  //http://jsfiddle.net/ukRsp/1397/
        function initMap() {
            // var myLatlng = new google.maps.LatLng(22.3016616, 114.1577151);
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: {lat: 22.3016616, lng: 114.1577151}  // Center the map on Hong Kong.
            });

            if ( $('.tgl-btn').click(function(){

                poly = new google.maps.Polyline({
                    strokeColor: '#3d00f7',
                    strokeOpacity: 1,
                    strokeWeight: 3
                });
                poly.setMap(map);
                polyIndex = 0;
                //Mirrors the path array to find index to delete and such
                mirrorCoordinates = [];
                markers = [];
                encodeString =[];
                // path;

                // useGPX();
                // location.replace("{{ url('/') }}/event/{{$event_id}}/gpx-route");
                $('#undo').hide();
                $('#save').hide();

                var gpxData = {!!$gpxData!!};
                for(var key in gpxData){
                    gpxLat = parseFloat(gpxData[key]["latitude"]);
                    gpxLng = parseFloat(gpxData[key]["longitude"]);
                    addLatLngInit(new google.maps.LatLng(gpxLat, gpxLng));
                }
                var bounds = new google.maps.LatLngBounds();
                for (var i = 0; i < markers.length; i++) {
                    bounds.extend(markers[i].getPosition());
                }
                map.fitBounds(bounds);

                google.maps.event.clearListeners(map, 'click');
            }));

            var decodedPath = google.maps.geometry.encoding.decodePath('{{$data->route}}');
            // var decodedLevels = decodeLevels("BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB");

            poly = new google.maps.Polyline({
                strokeColor: '#3d00f7',
                strokeOpacity: 1,
                strokeWeight: 3
            });
            poly.setMap(map);

            for (var key in decodedPath) {
                addLatLngInit(decodedPath[key]);

             }

            var bounds = new google.maps.LatLngBounds();
            for (var i = 0; i < markers.length; i++) {
                bounds.extend(markers[i].getPosition());
            }
            map.fitBounds(bounds);

            map.addListener('click', addLatLng);
            // console.log(decodedPath);
        }

        // function decodeLevels(encodedLevelsString) {
        //     var decodedLevels = [];

        //     for (var i = 0; i < encodedLevelsString.length; ++i) {
        //         var level = encodedLevelsString.charCodeAt(i) - 63;
        //         decodedLevels.push(level);
        //     }
        //     return decodedLevels;
        // }

    @endif

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
            map: map
        });
        markers.push(marker);
        // console.log(markers);
    }

    // Handles click events on a map, and adds a new point to the Polyline.
    function addLatLngInit(position) {
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
            map: map
        });
        markers.push(marker);
        // console.log(markers);
    }


    function useGPX(){
            // var myLatlng = new google.maps.LatLng(22.3016616, 114.1577151);
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: {lat: 22.3016616, lng: 114.1577151}  // Center the map on Hong Kong.
            });


            poly = new google.maps.Polyline({
                strokeColor: '#3d00f7',
                strokeOpacity: 1,
                strokeWeight: 3
            });
            poly.setMap(map);

            var gpxData = {!!$gpxData!!};
            for(var key in gpxData){
                gpxLat = parseFloat(gpxData[key]["latitude"]);
                gpxLng = parseFloat(gpxData[key]["longitude"]);
                // test.push(new google.maps.LatLng(gpxLat, gpxLng));
                // test.push = ({lat: gpxLat, lng:gpxLng});
                addLatLngInit(new google.maps.LatLng(gpxLat, gpxLng));
               
            }


            var bounds = new google.maps.LatLngBounds();
            for (var i = 0; i < markers.length; i++) {
                bounds.extend(markers[i].getPosition());
            }
            map.fitBounds(bounds);

            map.addListener('click', addLatLng);
    }

    // $('.tgl-btn').click(function(){
    //     // useGPX();
    //     // location.replace("{{ url('/') }}/event/{{$event_id}}/gpx-route");
    //     $('#undo').hide();
    //     $('#save').hide();
    // });
    // console.log(gpxData);
    //Remove the Polyline and the previous marker
    $('#undo').click(function(){
        mirrorCoordinates.pop();
        polyIndex--;
        poly.getPath().setAt(polyIndex, mirrorCoordinates[mirrorCoordinates.length - 1]);
        poly.getPath().removeAt(polyIndex);

        // console.log(markers.length);

        var markerIndex = markers.length - 1;
        markers[markerIndex].setMap(null);
        markers.pop();
    });

    $('#save').click(function(e){
        e.preventDefault();
        encodeString = google.maps.geometry.encoding.encodePath(path);
        $('#route').val(encodeString);
        document.getElementById('save-route').submit();
    });
    </script>

    <!-- Google Maps -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4i5s_R4E6Y8c5m4pEVxeVQvCJorm4MaI&libraries=geometry&callback=initMap"></script>
@endsection
