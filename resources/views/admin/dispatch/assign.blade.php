@extends('admin.layout.base')
<!-- @section('title', 'Dispatcher ') -->
@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <h4>Dispatcher</h4>
        <div class="dispatch-head">
            <nav class="navbar navbar-light bg-white b-a mb-2">
                <button class="navbar-toggler hidden-md-up" data-toggle="collapse" data-target="#process-filters" aria-controls="process-filters" aria-expanded="false" aria-label="Toggle Navigation"></button>
                <form class="form-inline navbar-item ml-1 float-xs-right">
                    <div class="input-group">
                        <input type="text" class="form-control b-a" placeholder="Search for...">
                        <span class="input-group-btn">
                        		<button type="submit" class="btn btn-primary b-a"><i class="ti-search"></i></button>
                        	</span>
                    </div>
                </form>
                <ul class="nav navbar-nav float-xs-right">
                    <li class="nav-item">
                        <a href="{{route('admin.dispatcher.add')}}">
                            <button type="button" class="btn btn-success btn-md label-right b-a-0 waves-effect waves-light"><span class="btn-label"><i class="ti-plus"></i></span>ADD</button>
                        </a>
                    </li>
                </ul>
                <div class="collapse navbar-toggleable-sm" id="process-filters">
                    <ul class="nav navbar-nav dispatcher-nav">
                <div class="collapse navbar-toggleable-sm" id="process-filters">
                    <ul class="nav navbar-nav dispatcher-nav">
                        <li class="nav-item"><a class="nav-link" href="{{route('admin.dispatcher.trips')}}">Open</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{route('admin.dispatcher.trips', array('type' => 'ONGOING'))}}">Ongoing</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{route('admin.dispatcher.trips', array('type' => 'SCHEDULED'))}}">Scheduled</a></li>
                    </ul>
                </div>
                    </ul>
                </div>
            </nav>
        </div>
        <div class="dispatch-content row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header text-uppercase"><b>Nearby Providers</b></div>
                    <div class="items-list">
                        @foreach($Providers as $Provider)
                        	<!-- Item Block Starts -->
                            <div class="il-item">
                                <a class="text-black" href="javascript:void(0)" onclick="assignProviderPopPicked({{ $Provider }})">
                                    <div class="media">
                                        <div class="media-body">
                                            <h5 class="mb-0-5">
                                                {{ $Provider->first_name.' '.$Provider->last_name }}
                                                <span class="tag tag-info pull-right">Online</span>
                                            </h5>
                                            <!-- <h6 class="media-heading">Address: Florida, United States</h6> -->
                                            <h6 class="media-heading">Phone No: @if(Setting::get('demo_mode', 0) == 1) {{ substr($Provider->mobile, 0, 5).'****' }} @else {{ $Provider->mobile }} @endif</h6>
                                            <h6 class="media-heading">Email: @if(Setting::get('demo_mode', 0) == 1) {{ substr($Provider->email, 0, 3).'****'.substr($Provider->email, strpos($Provider->email, "@")) }} @else {{$Provider->email}} @endif</h6>
                                        </div>
                                    </div>
                                </a>
                                <a class="btn btn-success mt-1" href="{{ route('admin.dispatcher.assign', array('trip' => $UserRequest->id, 'provider' => $Provider->id)) }}">Assign Provider</a>
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
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: 0, lng: 0},
            zoom: 2,
        });
        assignProviderShow({!! json_encode($Providers) !!}, {!! json_encode($UserRequest) !!});
    }

    function assignProviderShow(providers, trip) {
        console.log('assignProviderShow', providers)

        var bounds = new google.maps.LatLngBounds();
        bounds.extend({lat: trip.s_latitude, lng: trip.s_longitude});
        bounds.extend({lat: trip.d_latitude, lng: trip.d_longitude});

        providers.forEach(function(provider) {
            var marker = new google.maps.Marker({
                position: {lat: provider.latitude, lng: provider.longitude},
                map: map,
                provider_id: provider.id,
                title: provider.first_name + " " + provider.last_name,
                icon: '/asset/img/marker-car.png'
            });

            var content = "<p>Name : "+provider.first_name+" "+provider.last_name+"</p>"+
                    "<p>Rating : "+provider.rating+"</p>"+
                    "<p>Service Type : "+provider.service.service_type.name+"</p>"+
                    "<p>Car Model  : "+provider.service.service_type.name+"</p>"+
                    "<a href='/admin/dispatcher/trips/"+trip.id+'/'+provider.id+"' class='btn btn-success'>Assign this Provider</a>";

            marker.infowindow = new google.maps.InfoWindow({
                content: content
            });

            marker.addListener('click', function(){ 
                marker.infowindow.open(map, marker);
            });

            bounds.extend(marker.getPosition());
            mapMarkers.push(marker);
            
        });

        map.fitBounds(bounds);
    }

    function assignProviderPopPicked(provider) {
        var index;
        for (var i = mapMarkers.length - 1; i >= 0; i--) {
            if(mapMarkers[i].provider_id == provider.id) {
                index = i;
            }
            mapMarkers[i].infowindow.close();
        }
        console.log('index', index);
        // mapMarkers[index].setPosition({lat: provider.latitude, lng: provider.longitude});
        mapMarkers[index].infowindow.open(map, mapMarkers[index]);
    }
</script>
<script src="//maps.googleapis.com/maps/api/js?key={{ Setting::get('map_key', '') }}&libraries=places&callback=initMap" async defer></script>
@endsection