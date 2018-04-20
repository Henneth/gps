@extends('app')

@section('htmlheader_title')
    Replay Tracking
@endsection

@section('contentheader_title')
    Replay Tracking
@endsection

@section('main-content')
<div>

    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li id="home-tab" class="active"><a href="#" data-toggle="tab">Map</a></li>
            <li id="profile-tab" ><a href="#" data-toggle="tab">Athletes</a></li>
        </ul>
        <div class="tab-content">
            <div class="map-section tab-pane active">
                <div class="flex-container form-group">
                    <button type="button" class="replay-controls play btn btn-primary">Play</button>
                    <button type="button" class="replay-controls pause btn btn-default" disabled>Pause</button>
                    <button type="button" class="replay-controls stop btn btn-default" disabled>Stop</button>
                    <div class="slider-wrapper">
                        <input type="text" value="" class="slider form-control" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="0" data-slider-orientation="horizontal" data-slider-selection="before" data-slider-tooltip="show" data-slider-id="aqua" autocomplete="off">
                    </div>
                </div>
                <div id="map"></div>
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

@section('js')
    <!-- Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4i5s_R4E6Y8c5m4pEVxeVQvCJorm4MaI&libraries=geometry"></script>

    <!-- RichMarker -->
    <script src="{{ asset('/js/richmarker-compiled.js') }}" type="text/javascript"></script>

    <script>
        function initMap() {

            data = {!! $data !!};
            console.log(data);

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

                    google.maps.event.addListener(marker, 'click', function (marker) {
            			return function () {
                            var html = '<div>Bib Number: <b>' + content['bib_number'] + '</b></div>';
                            html += '<div>First Name: <b>' + content['first_name'] + '</b></div>';
                            html += '<div>Last Name: <b>' + content['last_name'] + '</b></div>';
                            html += '<div>Device ID: <b>' + content['device_id'] + '</b></div>';
                            html += '<div>Location: <b>' + location['lat'] + ', ' + location['lng'] + '</b></div>';
            				infowindow.setContent(html);
            				infowindow.open(map, marker);
            			}
            		}(marker));

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

                    var decodedPath = google.maps.geometry.encoding.decodePath('{{$route}}');
                    tempmarkers = [];
                    for (var key in decodedPath) {
                        addLatLngInit(decodedPath[key]);
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
                markers = [];

                // check device_id in localStorage 
                var temp = localStorage.getItem("visibility");
                var array = jQuery.parseJSON( temp ); 
                // console.log(" array: " + array );


                for (var key in data) {
                    // console.log(data[key]);
                    if (typeof data[key][0] != "undefined") {
                        var location = {lat: parseFloat(data[key][0]['latitude_final']), lng: parseFloat(data[key][0]['longitude_final'])};

                        if (temp !== null) { // localStorage is not empty
                            if (jQuery.inArray(data[key][0]['device_id'], array) !== -1) {
                                markers[data[key][0]['device_id']] = (addMarker(location, map, data[key][0]));
                            }
                        } else {
                            if (data[key][0]['status'] == "visible"){
                                markers[data[key][0]['device_id']] = (addMarker(location, map, data[0]));
                            }
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

            // setInterval(function()
            // {
            //     $.ajax({
            //         type:"get",
            //         url:"{{url('/')}}/event/{{$event_id}}/live-tracking/poll",
            //         dataType:"json",
            //         success:function(data)
            //         {
            //             var array = data;
            //             console.log(array);
            //             for (var key in array) {
            //                 console.log(array[key]['device_id']);
            //                 markers[array[key]['device_id']].setPosition( new google.maps.LatLng(parseFloat(array[key]['latitude_final']), parseFloat(array[key]['longitude_final'])) );
            //             }
            //         }
            //     });
            // }, 3000);//time in milliseconds
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
                setMap: map
            });
            tempmarkers.push(marker);
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
                return value + '%';
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
            console.log(dateString);

            for (var device_id in data) {
                if (markers[device_id]) {
                    console.log(device_id);
                    var markerHasData = false;
                    markers[device_id].setVisible(true);
                    for (var i in data[device_id]) {
                        if (data[device_id][i]['timestamp'] <= time) {
                            console.log(data[device_id][i]['timestamp']);
                            markers[device_id].setPosition( new google.maps.LatLng(parseFloat(data[device_id][i]['latitude_final']), parseFloat(data[device_id][i]['longitude_final'])) );
                            markerHasData = true;
                            break;
                        }
                    }
                    if (!markerHasData) {
                        markers[device_id].setVisible(false);
                    }
                }
            }
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

        // Flip tags
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
    })
    </script>
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
    </style>
@endsection
