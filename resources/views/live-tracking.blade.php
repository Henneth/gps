@extends('app')

@section('htmlheader_title')
    Live Tracking
@endsection

@section('contentheader_title')
    Live Tracking
@endsection

@section('main-content')
<div class="container-flex">
    <div class="form-group" style="color: #666; float: left;">Athletes' latest locations from <b>{{$event->datetime_from}}</b> to <b>{{$event->datetime_to}}</b></div>
    {{-- <div style="color: #666; float: right;">Current Time: <b><span id="time"></span></b></div> --}}
    <div id="map"></div>
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
    </style>
@endsection

@section('js')
    <!-- Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4i5s_R4E6Y8c5m4pEVxeVQvCJorm4MaI&libraries=geometry"></script>

    <!-- RichMarker -->
    <script src="{{ asset('/js/richmarker-compiled.js') }}" type="text/javascript"></script>

    <script>

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
                    var borderStyle = '<style>.id' + content['device_id'] + '.label_content:after { border-top: solid 8px ' + content['colour_code'] + '; }</style>';
                    var marker = new RichMarker({
                        map: map,
                        flat: true,
                        position: new google.maps.LatLng(parseFloat(content['latitude_final']), parseFloat(content['longitude_final'])),
                        content: borderStyle + '<div><div class="id' + content['device_id'] + ' label_content" style="background-color: ' + content['colour_code'] + '">' + content['bib_number']
                        + '</div></div>'
                    });


                    google.maps.event.addListener(marker, 'click', (function (marker, i) {
            			return function () {
                            var html = '<div>Bib Number: <b>' + content['bib_number'] + '</b></div>';
                            html += '<div>First Name: <b>' + content['first_name'] + '</b></div>';
                            html += '<div>Last Name: <b>' + content['last_name'] + '</b></div>';
                            html += '<div>Device ID: <b>' + content['device_id'] + '</b></div>';
                            html += '<div>Location: <b>' + location['lat'] + ', ' + location['lng'] + '</b></div>';
            				infowindow.setContent(html);
            				infowindow.open(map, marker);
            			}
            		})(marker, i));

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

                // set route
                poly = new google.maps.Polyline({
                    strokeColor: '#3d00f7',
                    strokeOpacity: 1,
                    strokeWeight: 3,
                    map: map
                });

                var decodedPath = google.maps.geometry.encoding.decodePath('{{$route->route}}'); 
                tempmarkers = [];
                for (var key in decodedPath) {
                    addLatLngInit(decodedPath[key]);
                }

                var bounds = new google.maps.LatLngBounds();
                for (var i = 0; i < tempmarkers.length; i++) {
                    bounds.extend(tempmarkers[i].getPosition());
                }
                map.fitBounds(bounds);

                // set InfoWindow pixelOffset
                var infowindow = new google.maps.InfoWindow({
                    pixelOffset: new google.maps.Size(0, -36),
                });

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
                        var array = data;
                        // console.log(array);
                        for (var key in array) {
                            // console.log(array[key]['device_id']);
                            markers[array[key]['device_id']].setPosition( new google.maps.LatLng(parseFloat(array[key]['latitude_final']), parseFloat(array[key]['longitude_final'])) );
                        }
                    }
                });
            }, 3000);//time in milliseconds
        }
        initMap();

        function addLatLngInit(position) {
          path = poly.getPath();

          // Because path is an MVCArray, we can simply append a new coordinate
          // and it will automatically appear.
          path.push(position);

          // mirrorCoordinates.push(position);
          // polyIndex++;

          // // Get the current Zoom Level and Center of the area 
          // var tempZoom = map.getZoom();
          // var tempLat = map.getCenter().lat();
          // var tempLng = map.getCenter().lng();
          // // console.log(tempLat, tempLng, tempZoom);


          // Add a new marker at the new plotted point on the polyline.
          var marker = new google.maps.Marker({
            position: position,
            title: '#' + path.getLength(),
            map: map
          });
          tempmarkers.push(marker);

        }


    </script>
@endsection
