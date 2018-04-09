@extends('app')

@section('htmlheader_title')
    Live Tracking
@endsection

@section('contentheader_title')
    Live Tracking
@endsection

@section('main-content')
<div class="container-flex">
    <div class="form-group" style="color: #666;">Last location of athletes within {{$event->datetime_from}} - {{$event->datetime_to}}</div>
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

            data = {!! $data !!};
            console.log(data);

            @if ($data)
                // Function to add a marker to the map.
                function addMarker(location, map, content) {
                    // Add the marker at the clicked location, and add the next-available label
                    // from the array of alphabetical characters.
                    function pinSymbol(color) {
                        return {
                            path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M -2,-30 a 2,2 0 1,1 4,0 2,2 0 1,1 -4,0',
                            fillColor: color,
                            fillOpacity: 0.7,
                            // strokeColor: '#000',
                            strokeWeight: 2,
                            scale: 1,
                       };
                    }
// https://developers.google.com/maps/documentation/javascript/reference/3.exp/marker#Icon.labelOrigin
                    var marker = new google.maps.Marker({
                        position: location,
                        label: content['bib_number'],
                        map: map,
                        icon: pinSymbol("green"),
                    });

                    google.maps.event.addListener(marker, 'click', (function (marker, i) {
            			return function () {
                            var html = '<div>Bib Number: <b>' + content['bib_number'] + '</b></div>';
                            html += '<div>Given Name: <b>' + content['first_name'] + '</b></div>';
                            html += '<div>Family Name: <b>' + content['last_name'] + '</b></div>';
                            html += '<div>Device ID: <b>' + content['device_id'] + '</b></div>';
                            html += '<div>Location: <b>' + location['lat'] + location['lng'] + '</b></div>';
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

                // Locations
                {{--var locations = [
                    @foreach ($data as $key => $datum)
                        [{{$datum->device_id}}, { lat: {{$datum->latitude_final}}, lng: {{$datum->longitude_final}} }, { bib_number: '{{$datum->bib_number}}', given_name: '{{$datum->first_name}}', family_name: '{{$datum->last_name}}', device_id: '{{$datum->device_id}}' }]{{ $key == count($data) - 1 ? '' : ',' }}
                    @endforeach
                ]--}}

                // Add Markers
                var markers = [];
                for (var i = 0; i < data.length; i++) {
                    var location = {lat: parseFloat(data[i]['latitude_final']), lng: parseFloat(data[i]['longitude_final'])};
                    markers[data[i]['device_id']] = (addMarker(location, map, data[i]));
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
