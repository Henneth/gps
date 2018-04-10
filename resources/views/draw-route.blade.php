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
      .data-form {
      	display: inline-block;
      }
    </style>
@endsection

@section('main-content')
	@if (session('status'))
		<div class="box box-success box-solid">
			<div class="box-header with-border">
				<h3 class="box-title">Annoucement</h3>
				<div class="box-tools pull-right">
					<button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
				</div>
				<!-- /.box-tools -->
			</div>
			<!-- /.box-header -->
			<div class="box-body">
				{{ session('status') }}
			</div>
			<!-- /.box-body -->
		</div>
		<!-- /.box -->
	@endif
	<div>
		<button type="button" class="btn btn-default" id="undo"><i class="fa fa-undo"></i> Undo</button>
		<div class="data-form">
			<form id="save-route" action="{{ url('/') }}/event/{{$event_id}}/save-route" method="POST">
				{{ csrf_field() }}
				<input type="hidden" id="route" name="route">
				<button type="sumbit" class="btn btn-primary" id="save"><i class="far fa-save"></i> Save Route</button>
			</form>
		</div>
	</div>
	<div class="container-flex">
	    <div id="map"></div>
	</div>
@endsection

@section('js')
    <script>
    {{-- {{ $data->route }} --}}



      var poly;
      var map;
  	  var polyIndex = 0;
	  //Mirrors the path array to find index to delete and such
	  var mirrorCoordinates = [];
	  var markers = [];
	  var encodeString;
	  var path;

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

	@else

	  function initMap() {
	  	// Decode the encodeString
	    var decode = google.maps.geometry.encoding.decodePath("{{$data->route}}");
        // console.log(encodeString);
        console.log("DECODE" + decode);

        map = new google.maps.Map(document.getElementById('map'), {
          zoom: 12,
          center: {lat: 22.3016616, lng: 114.1577151}  // Center the map.

        });
        poly = new google.maps.Polyline({
          paths: decode,
          strokeColor: '#3d00f7',
          strokeOpacity: 1,
          strokeWeight: 3
        });
        poly.setMap(map);


        // Add a listener for the click event
        map.addListener('click', addLatLng);


		// var markers = [];//some array
		// var bounds = new google.maps.LatLngBounds();
		// for (var i = 0; i < markers.length; i++) {
		//  bounds.extend(markers[i].getPosition());
		// }
		// map.fitBounds(bounds);



    var infowindow = new google.maps.InfoWindow();

    var marker, i;
	var locations = [
		[22.54162, 114.07385000000001, 4],
		[22.524820000000002, 114.14663000000002, 5],
		[22.477240000000002, 114.11882000000001, 3],
		[22.47089, 114.06732000000001, 2],
		// ['Maroubra Beach', -33.950198, 151.259302, 1]
	];

    for (i = 0; i < locations.length; i++) {  
      marker = new google.maps.Marker({
        position: new google.maps.LatLng(locations[i][0], locations[i][1]),
        map: map
      });

      google.maps.event.addListener(marker, 'click', (function(marker, i) {
        return function() {
          infowindow.setContent(locations[i][0]);
          infowindow.open(map, marker);
        }
      })(marker, i));
    }


      }

	@endif

      // Handles click events on a map, and adds a new point to the Polyline.
      function addLatLng(event) {
        path = poly.getPath();

        // Because path is an MVCArray, we can simply append a new coordinate
        // and it will automatically appear.
        path.push(event.latLng);

		mirrorCoordinates.push(event.latLng);
		polyIndex++;

        // Get the current Zoom Level and Center of the area 
        var tempZoom = map.getZoom();
        var tempLat = map.getCenter().lat();
        var tempLng = map.getCenter().lng();
        // console.log(tempLat, tempLng, tempZoom);


        // Add a new marker at the new plotted point on the polyline.
        var marker = new google.maps.Marker({
          position: event.latLng,
          title: '#' + path.getLength(),
          map: map
        });
        markers.push(marker);
        // console.log(markers);
	  }

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

	 //  var bounds = new google.maps.LatLngBounds();
		// for (var i = 0; i < markers.length; i++) {
		//  bounds.extend(markers[i].getPosition());
		// }
		// map.fitBounds(bounds);
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
