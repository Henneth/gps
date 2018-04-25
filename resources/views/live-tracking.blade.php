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
            <li id="profile-tab" ><a href="#" data-toggle="tab">Athletes</a></li>
        </ul>
        <div class="tab-content">
            <div class="map-section tab-pane active">
                <div class="form-group" style="color: #666; float: left;">Athletes' latest locations from <b>{{$event->datetime_from}}</b> to <b>{{$event->datetime_to}}</b></div>
                {{-- <div style="color: #666; float: right;">Current Time: <b><span id="time"></span></b></div> --}}
                <div id="map"></div> {{-- google map here --}}
            </div>
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
    </style>
@endsection

@section('js')
    <!-- Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4i5s_R4E6Y8c5m4pEVxeVQvCJorm4MaI&libraries=geometry"></script>

    <!-- RichMarker -->
    <script src="{{ asset('/js/richmarker-compiled.js') }}" type="text/javascript"></script>

    <script>
        var map;
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

                    google.maps.event.addListener(marker, 'click', (function (marker) {
            			return function () {
                            var html = '<div>Bib Number: <b>' + content['bib_number'] + '</b></div>';
                            html += '<div>First Name: <b>' + content['first_name'] + '</b></div>';
                            html += '<div>Last Name: <b>' + content['last_name'] + '</b></div>';
                            html += '<div>Device ID: <b>' + content['device_id'] + '</b></div>';
                            html += '<div>Location: <b>' + location['lat'] + ', ' + location['lng'] + '</b></div>';
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

                    var data = {!!$route->route!!};
                    // console.log(data);
                    tempmarkers = [];
                    for(var key in data){
                        gpxLat = parseFloat(data[key]["lat"]);
                        gpxLng = parseFloat(data[key]["lon"]);
                        addLatLngInit(new google.maps.LatLng(gpxLat, gpxLng));
                    }

                    var bounds = new google.maps.LatLngBounds();
                    for (var i = 0; i < tempmarkers.length; i++) {
                        bounds.extend(tempmarkers[i].getPosition());
                    }
                    map.fitBounds(bounds);
                @endif

                // set InfoWindow pixelOffset
                var infowindow = new google.maps.InfoWindow({
                    pixelOffset: new google.maps.Size(0, -36),
                });

                // Add Markers
                var markers = [];
                // check device_id in localStorage
                var temp = localStorage.getItem("visibility");
                var array = jQuery.parseJSON( temp );
                console.log(" array: " + array );

                for (var i = 0; i < data.length; i++) {
                    var location = {lat: parseFloat(data[i]['latitude_final']), lng: parseFloat(data[i]['longitude_final'])};

                    console.log(data[i]['device_id']);
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
                        var array = data;
                        // console.log(array);
                        for (var key in array) {
                            // console.log(array[key]['device_id']);
                            if (markers[array[key]['device_id']]) {
                                markers[array[key]['device_id']].setPosition( new google.maps.LatLng(parseFloat(array[key]['latitude_final']), parseFloat(array[key]['longitude_final'])) );
                            }
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

            // Add a new marker at the new plotted point on the polyline.
            var marker = new google.maps.Marker({
                position: position,
                title: '#' + path.getLength(),
                map: map
            });
            tempmarkers.push(marker);
        }

        $('#profile-tab').click(function(){
            $('.map-section').removeClass('active');
            $('.profile-section').addClass('active');
        })
        $('#home-tab').click(function(){
            $('.map-section').addClass('active');
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
            var dataID = localStorage.getItem("visibility");

            // check localStorage existing
            if (dataID) {
                console.log(dataID);

                // json decode localStorage
                var array = jQuery.parseJSON( dataID );

                // Clean all default atrr "checked"
                $('.tgl').removeAttr('checked');

                for (var i = array.length - 1; i >= 0; i--) {
                    $('.tgl[data-id="'+array[i]+'"]').prop("checked","checked");
                }
            }
        }

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
            localStorage.setItem("visibility", json);

        })


    </script>
@endsection
