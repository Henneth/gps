@extends('app')

@section('htmlheader_title')
    Checkpoint
@endsection

@section('contentheader_title')
    Checkpoint
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

		{{-- <input type="button" value="Delete" onclick="DeleteMarkers()" /> --}}
		<div class="data-form">
			<form id="save-checkpoint" action="{{ url('/') }}/event/{{$event_id}}/save-checkpoint" method="POST">
				{{ csrf_field() }}
				<input type="hidden" id="checkpoint" name="checkpoint">
				<button type="sumbit" class="btn btn-primary" id="save"><i class="far fa-save"></i> Save Checkpoint</button>
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
	  var mirrorCoordinates = []; //Mirrors the path array to find index to delete and such
	  var markers = [];
	  var encodeString;
	  var path;

    @if(!$data->route)
      function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
          zoom: 12,
          center: {lat: 22.3016616, lng: 114.1577151}  // Center the map
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
          map = new google.maps.Map(document.getElementById('map'), {
            zoom: 12,
            center: {lat: 22.3016616, lng: 114.1577151}  // Center the map
          });
              
          var decodedPath = google.maps.geometry.encoding.decodePath('{{$data->route}}'); 

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
      }
	@endif


	// <----  Work without route data ! ! ! ! ---->
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


	// <----  Work with route data ! ! ! ! ---->
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
        // map: map
      });
      markers.push(marker);
    }

	  //<---- Add label on map ---->
      // Each marker is labeled with a single alphabetical character.
      var labelIndex = 1;
      var markerList = []; //array to store marker
      var num = 1; // point number
      var pointPosition = []; // array for longitude & latitude
      var uniqueId = 1;
      var myJSON; 

    window.onload = function () {

 		  labelIndex = 1; 
	    //Attach click event handler to the map.
	    google.maps.event.addListener(map, 'click', function (e) {


        labelIndex = pointPosition.length + 1;

        //Determine the location where the user has clicked.
        var location = e.latLng;

        var marker = new google.maps.Marker({
          position: location,
          label: String(labelIndex++),
          map: map,
          // title:'#point-' + [num++]
        }); 

        //Set unique id
        marker.id = uniqueId;
        uniqueId++;

        //Attach click event handler to the marker.
        google.maps.event.addListener(marker, "click", function (e) {
            var content = 'Latitude: ' + location.lat() + '<br />Longitude: ' + location.lng();
            content += "<br /><input type = 'button' va;ue = 'Delete' onclick = 'DeleteMarker(" + marker.id + ");' value = 'Delete' />";
            var infoWindow = new google.maps.InfoWindow({
                content: content
            });
            infoWindow.open(map, marker);
        });

        // Get the current location of label to store into an array
        var mLat = location.lat(marker);
        var mLng = location.lng(marker);
        var mPosition = {latitude: mLat, longitude: mLng};
        pointPosition.push(mPosition);  

        //Add marker to the array.
        markerList.push(marker);
	    });

      // ------  show label from DB on map.  ------
      // console.log({!!$allCheckpoints!!});
      var checkpoints = {!! $allCheckpoints !!};
      markerList= []; // empty the list bf for loop

      for(var key in checkpoints ){

        var CPlat = parseFloat(checkpoints[key]['latitude']); 
        var CPlng = parseFloat(checkpoints[key]['longitude']); 
        var location = new google.maps.LatLng({'lat': CPlat, 'lng': CPlng});

        // show labels on map
        var marker = new google.maps.Marker({
          position: location,
          // label: String(labelIndex++),
          label: {text: String(labelIndex++), color: "white"},
          map: map,
        }); 

        // //Set unique id
        // marker.id = uniqueId;
        // uniqueId++;

        // console.log(marker);
        // // Attach click event handler to the marker.
        // google.maps.event.addListener(marker, "click", function (e) {
        //     var content = 'Latitude: ' + location.lat() + '<br />Longitude: ' + location.lng();
        //     console.log(marker.id);
        //     content += "<br /><input type = 'button' va;ue = 'Delete' onclick = 'DeleteMarker(" + marker.id + ");' value = 'Delete' />";
        //     var infoWindow = new google.maps.InfoWindow({
        //         content: content
        //     });
        //     infoWindow.open(map, marker);
        // }); 

        // // Get current label and store into an array
        // var mLat = location.lat(marker);
        // var mLng = location.lng(marker);
        // var mPosition = {latitude: mLat, longitude: mLng};
        // pointPosition.push(mPosition); // DEBUG PointPosition list is correct

        // //Add marker to the array.
        // markerList.push(marker);

      }
    };


    function DeleteMarker(id) {
        //Find and remove the marker from the Array
        for (var i = 0; i < markerList.length; i++) {
            if (markerList[i].id == id) {
                //Remove the marker from Map                  
                markerList[i].setMap(null);

                //Remove the marker from array.
                markerList.splice(i, 1);

                pointPosition.splice(i, 1);

                myJSON = JSON.stringify(pointPosition);

                // console.log(makerList);
                for (var j = i; j < markerList.length; j++) {
        				  markerList[j].setLabel(String(j+1));
                }
                return;
            }
			// markerList[i].setTitle('new title');
        }
    };

	$('#save').click(function(e){
	  	e.preventDefault();
		  myJSON = JSON.stringify(pointPosition);
	  	$('#checkpoint').val(myJSON);
	  	document.getElementById('save-checkpoint').submit();
	});

    </script>

    <!-- Google Maps -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4i5s_R4E6Y8c5m4pEVxeVQvCJorm4MaI&libraries=geometry&callback=initMap"></script>
@endsection
