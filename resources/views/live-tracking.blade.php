@extends('app')

@section('htmlheader_title')
    Live Tracking
@endsection

@section('contentheader_title')
    Live Tracking
@endsection

@section('main-content')
<div class="container-flex">
	{{-- <div class="alert alert-danger alert-dismissible">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<h4><i class="icon fa fa-ban"></i> Error!</h4>
		{{session('error')}}
	</div> --}}
    <div id="map"></div>
</div>
@endsection

@section('css')
    <style>
        #map {
            height:80vh;
            width: 100%;
        }
    </style>
@endsection

@section('js')
    <script>
        function initMap() {
            @if ($data)
                // Function to add a marker to the map.
                function addMarker(location, map, content) {
                    // Add the marker at the clicked location, and add the next-available label
                    // from the array of alphabetical characters.
                    var marker = new google.maps.Marker({
                        position: location,
                        label: labels[labelIndex++ % labels.length],
                        map: map
                    });

                    google.maps.event.addListener(marker, 'click', (function (marker, i) {
            			return function () {
                            var html = '<div>Bib Number: <b>' + content['bib_number'] + '</b></div>';
                            html += '<div>Given Name: <b>' + content['given_name'] + '</b></div>';
                            html += '<div>Family Name: <b>' + content['family_name'] + '</b></div>';
                            html += '<div>Device ID: <b>' + content['device_id'] + '</b></div>';
                            html += '<div>Latitude: <b>' + content['latitude_final'] + '</b></div>';
                            html += '<div>Longitude: <b>' + content['longitude_final'] + '</b></div>';
            				infowindow.setContent(html);
            				infowindow.open(map, marker);
            			}
            		})(marker, i));

                    return marker;
                }

                var map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 13,
                    // center: {lat: 22.404767, lng: 114.1057550}
                    center: {lat: 22.3016616, lng: 114.1577151}
                });

                var infowindow = new google.maps.InfoWindow();

                // Create an array of alphabetical characters used to label the markers.
                var labels = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                var labelIndex = 0;

                // Locations
                var locations = [
                    @foreach ($data as $key => $datum)
                        [{{$datum->device_id}}, { lat: {{$datum->latitude_final}}, lng: {{$datum->longitude_final}} }, { bib_number: '{{$datum->bib_number}}', given_name: '{{$datum->given_name}}', family_name: '{{$datum->family_name}}' }]{{ $key == count($data) - 1 ? '' : ',' }}
                    @endforeach
                ]

                // Add Markers
                var markers = [];
                for (var i = 0; i < locations.length; i++) {
                    markers[locations[i][0]] = (addMarker(locations[i][1], map, locations[i][2]));
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
                        console.log('in');
                        var array = data;
                        console.log(array);
                        for (var key in array) {
                            console.log(array[key]['device_id']);
                            markers[array[key]['device_id']].setPosition({ lat: parseFloat(array[key]['latitude_final']), lng: parseFloat(array[key]['longitude_final'])});
                        }
                    }
                });
            }, 3000);//time in milliseconds
        }
    </script>
    {{-- <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script> --}}
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4i5s_R4E6Y8c5m4pEVxeVQvCJorm4MaI&callback=initMap">
    </script>
@endsection
