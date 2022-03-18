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
                        <li class="nav-item"><a class="nav-link" href="{{route('admin.dispatcher.trips')}}">Open</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{route('admin.dispatcher.trips', array('type' => 'ONGOING'))}}">Ongoing</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{route('admin.dispatcher.trips', array('type' => 'SCHEDULED'))}}">Scheduled</a></li>
                    </ul>
                </div>
            </nav>
        </div>
        <div class="dispatch-content row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card card-block" id="create-ride">
                        <h3 class="card-title text-uppercase">Ride Details</h3>
                        <form id="form-create-ride" method="POST" action="{{route('admin.dispatcher.store')}}">
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="col-xs-6">
                                        <div class="form-group">
                                        <label for="first_name">First Name</label>
                                        <input type="text" class="form-control" name="first_name" id="first_name" placeholder="First Name" required="">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Last Name" required="">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" name="email" id="email" placeholder="Email" required="">
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="mobile">Phone</label>
                                        <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Phone" required="">
                                    </div>
                                </div>
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <label for="s_address">Source Location</label>
                                        <input type="text" name="s_address" class="form-control" id="s_address" placeholder="Source Address" required="" autocomplete="off">
                                        <input type="hidden" name="s_latitude" id="s_latitude" value="">
                                        <input type="hidden" name="s_longitude" id="s_longitude" value="">
                                    </div>
                                    <div class="form-group">
                                        <label for="schedule_time">Schedule Date</label>
                                        <input type="text" id="schedule_time" class="form-control" id="datepicker-autoclose" placeholder="mm/dd/yyyy" name="schedule_time">

                                    </div>
                                    <div class="form-group">
                                        <label for="service_types">Service Type</label>
                                        <select name="service_type" class="form-control">
                                            @foreach(get_all_service_types() as $type)
                                                <option value="{{$type->id}}">{{$type->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="provider_auto_assign">Auto Assign Provider</label>
                                        <br>
                                        <input type="checkbox" id="provider_auto_assign" name="provider_auto_assign" class="js-switch" data-color="#f59345" value="on" style="display: none;" data-switchery="true">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <button type="button" class="btn btn-lg btn-danger btn-block waves-effect waves-light">CANCEL</button>
                                </div>
                                <div class="col-xs-6">
                                    <button class="btn btn-lg btn-success btn-block waves-effect waves-light">SUBMIT</button>
                                </div>
                            </div>
                        </form>
                    </div>                    
                </div>
            </div>
            <div class="col-md-8">
            	<div class="card my-card">
            		<div class="card-header text-uppercase"><b>MAP</b></div>
            		<div class="card-body">
            			<div id="map" style="width: 100%"></div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js"></script>
<script type="text/javascript">
    window.Tranxit = {!! json_encode([
        "minDate" => \Carbon\Carbon::today()->format('Y-m-d\TH:i'),
        "maxDate" => \Carbon\Carbon::today()->addDays(30)->format('Y-m-d\TH:i'),
        "map" => false,
    ]) !!}
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#schedule_time').datetimepicker({
            minDate: window.Tranxit.minDate,
            maxDate: window.Tranxit.maxDate,
        });
    });
</script>
<script type="text/javascript" src="{{ asset('asset/js/dispatcher-map-admin.js') }}"></script>
<script src="//maps.googleapis.com/maps/api/js?key={{ Setting::get('map_key', '') }}&libraries=places&callback=initMap" async defer></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/jquery.datetimepicker.min.css" />
@endsection