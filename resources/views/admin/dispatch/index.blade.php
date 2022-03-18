@extends('admin.layout.base')
<!-- @section('title', 'Dispatcher ') -->
@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <h4>Dispatcher</h4>
        <div class="dispatch-head">
            <nav class="navbar navbar-light bg-white b-a mb-2">
                <button class="navbar-toggler hidden-md-up" data-toggle="collapse" data-target="#process-filters" aria-controls="process-filters" aria-expanded="false" aria-label="Toggle Navigation"></button>
                <!-- <form class="form-inline navbar-item ml-1 float-xs-right">
                    <div class="input-group">
                        <input type="text" class="form-control b-a" placeholder="Search for...">
                        <span class="input-group-btn">
                        		<button type="submit" class="btn btn-primary b-a"><i class="ti-search"></i></button>
                        	</span>
                    </div>
                </form> -->
                <ul class="nav navbar-nav float-xs-right">
                    <li class="nav-item">
                        <a href="{{route('admin.dispatcher.add')}}">
                            <button type="button" class="btn btn-success btn-md label-right b-a-0 waves-effect waves-light"><span class="btn-label"><i class="ti-plus"></i></span>ADD</button>
                        </a>
                    </li>
                </ul>
                <div class="collapse navbar-toggleable-sm" id="process-filters">
                    <ul class="nav navbar-nav dispatcher-nav">
                        <li class="nav-item @if($Type == '') active @endif"><a class="nav-link" href="{{route('admin.dispatcher.trips')}}">Open</a></li>
                        <li class="nav-item @if($Type == 'ONGOING') active @endif"><a class="nav-link" href="{{route('admin.dispatcher.trips', array('type' => 'ONGOING'))}}">Ongoing</a></li>
                        <li class="nav-item @if($Type == 'SCHEDULED') active @endif"><a class="nav-link" href="{{route('admin.dispatcher.trips', array('type' => 'SCHEDULED'))}}">Scheduled</a></li>
                    </ul>
                </div>
            </nav>
        </div>
        <div class="dispatch-content row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header text-uppercase"><b>Open List</b></div>
                    <div class="items-list">
                        @foreach($Trips as $Trip)
                        	<!-- Item Block Starts -->
                            <div class="il-item">
                                <a class="text-black" @if($Type == '') href="{{ route('admin.dispatcher.getassign', $Trip->id) }}" @else href="javascript:void(0)" @endif onclick="ongoingInitialize({{$Trip}})">
                                    <div class="media">
                                        <div class="media-body">
                                            <h5 class="mb-0-5">
                                            	{{ @$Trip->user->first_name.' '.@$Trip->user->last_name }}
                                                <span class="tag @if($Type == 'ONGOING') tag-primary @elseif($Type == 'SCHEDULED') tag-info @else tag-warning @endif pull-right">{{$Type?$Type:'Open'}}</span></h5>
                                            <h6 class="media-heading">From: {{ @$Trip->s_address }}</h6>
                                            <h6 class="media-heading">User No: {{ @$Trip->user->mobile }}</h6>
                                            <h6 class="media-heading">Provider No: {{ @$Trip->provider->mobile }}</h6>
                                            <h6 class="media-heading">Description: {{ @$Trip->description }}</h6>
                                            <h6 class="media-heading">Payment: {{ $Trip->payment_mode }}</h6>
                                            <h6 class="media-heading">Service Type: {{ @$Trip->service_type->name }}</h6>
                                            @if(count($Trip->filter))
                                                <span class="text-muted">Auto Assignment : {{ $Trip->updated_at }}</span>
                                            @else
                                                <span class="text-muted">Manual Assignment : {{ $Trip->updated_at }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                                @if($Type == '')
                                    <a class="btn btn-danger mt-1" href="{{route('admin.dispatcher.cancel', array('request_id' => $Trip->id) )}}">Cancel Ride</a>
                                @endif
                            </div>
                            <!-- Item Block Ends -->
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-8">
            	<div class="card my-card">
            		<div class="card-header text-uppercase"><b>MAP</b></div>
            		<div class="card-body">
            			<div id="map" style="width: 100%">
                        </div>
            		</div>
            	</div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('styles')
<style type="text/css">
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    #map {
        height: 450px;
        position: relative;
        overflow: hidden;
    }
</style>
@endsection
@section('scripts')
<script>
    var map, mapMarkers = [];
    function initMap() {
        new google.maps.Map(document.getElementById('map'), {
            center: {lat: 0, lng: 0},
            zoom: 2,
        });
    }

    function ongoingInitialize(trip) {
        console.log('ongoingRidesInitialize', trip);
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: 0, lng: 0},
            zoom: 2,
        });

        var bounds = new google.maps.LatLngBounds();

        var marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29),
            icon: '/asset/img/marker-start.png'
        });

        var markerSecond = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29),
            icon: '/asset/img/marker-end.png'
        });

        source = new google.maps.LatLng(trip.s_latitude, trip.s_longitude);
        destination = new google.maps.LatLng(trip.d_latitude, trip.d_longitude);

        marker.setPosition(source);
        markerSecond.setPosition(destination);

        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true, preserveViewport: true});
        directionsDisplay.setMap(map);

        directionsService.route({
            origin: source,
            destination: destination,
            travelMode: google.maps.TravelMode.DRIVING
        }, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(result);

                marker.setPosition(result.routes[0].legs[0].start_location);
                markerSecond.setPosition(result.routes[0].legs[0].end_location);
            }
        });

        if(trip.provider) {
            var markerProvider = new google.maps.Marker({
                map: map,
                icon: "/asset/img/marker-car.png",
                anchorPoint: new google.maps.Point(0, -29)
            });

            provider = new google.maps.LatLng(trip.provider.latitude, trip.provider.longitude);
            markerProvider.setVisible(true);
            markerProvider.setPosition(provider);
            console.log('Provider Bounds', markerProvider.getPosition());
            bounds.extend(markerProvider.getPosition());
        }
        bounds.extend(marker.getPosition());
        bounds.extend(markerSecond.getPosition());
        map.fitBounds(bounds);
    }
</script>
<script src="//maps.googleapis.com/maps/api/js?key={{ Setting::get('map_key', '') }}&libraries=places&callback=initMap" async defer></script>
@endsection