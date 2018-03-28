@extends('app')

@section('htmlheader_title')
    Live Tracking
@endsection

@section('contentheader_title')
    Live Tracking
@endsection

@section('main-content')
<div class="container-flex">
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
            var uluru = {lat: {{$data->latitude_final}}, lng: {{$data->longitude_final}}};
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 14,
                center: uluru
            });
            var marker = new google.maps.Marker({
                position: uluru,
                map: map
            });
        }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD4i5s_R4E6Y8c5m4pEVxeVQvCJorm4MaI&callback=initMap">
    </script>
@endsection
