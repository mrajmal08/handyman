@extends('admin.layout.base')

@section('title', 'Request details ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
            	<h4>@lang('main.service') details</h4>
    	    <a href="{{ route('admin.request.history') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Back</a>

            	<br>
            	<br>

            		<div class="row">

		              <div class="col-md-6">

		                <dl class="row">

		                	<dt class="col-sm-4">Booking ID :</dt>
		                    <dd class="col-sm-8">{{$request->booking_id ? $request->booking_id : '-' }}</dd>

		                    <dt class="col-sm-4">@lang('main.user') Name :</dt>
		                    <dd class="col-sm-8">{{ $request->user ? $request->user->first_name : "User Deleted"}}</dd>

		                    <dt class="col-sm-4">@lang('main.provider') Name :</dt>
		                    <dd class="col-sm-8">{{ $request->provider ? $request->provider->first_name : "Not Assigned" }}</dd>

		                    @if($request->status == 'SCHEDULED')

		                    <dt class="col-sm-4">@lang('main.service') Scheduled Time :</dt>
		                    <dd class="col-sm-8">
		                    	@if($request->schedule_at != "")
		                     		{{date('jS \of F Y h:i:s A', strtotime($request->schedule_at)) }} 
		                     	@else
		                     		- 
		                     	@endif
		                     </dd>


		                     @else

		                     <dt class="col-sm-4">@lang('main.service') Start Time :</dt>
		                    <dd class="col-sm-8">
		                    	@if($request->started_at != "")
		                     		{{date('jS \of F Y h:i:s A', strtotime($request->started_at)) }} 
		                     	@else
		                     		- 
		                     	@endif
		                     </dd>

		                    <dt class="col-sm-4">@lang('main.service') End Time :</dt>
		                    <dd class="col-sm-8">
		                    	@if($request->finished_at != "") 
		                    		{{date('jS \of F Y h:i:s A', strtotime($request->finished_at)) }}
		                    	@else
		                    		- 
		                    	@endif
		                    </dd>

		                    @endif

		                    <dt class="col-sm-4">User Phone Number :</dt>
		                    <dd class="col-sm-8">{{$request->user->mobile ? $request->user->mobile : '-' }}</dd>

		                    <dt class="col-sm-4">Provider Phone Number:</dt>
		                    <dd class="col-sm-8">{{$request->provider->mobile ? $request->provider->mobile : '-' }}</dd>

		                    <dt class="col-sm-4">Service Type :</dt>
		                    <dd class="col-sm-8">{{$request->service_type->name ? $request->service_type->name : '-' }}</dd>
		                   

		                    <dt class="col-sm-4">@lang('main.service') Location :</dt>
		                    <dd class="col-sm-8">{{$request->s_address ? $request->s_address : '-' }}</dd>

		                    @if($request->payment != "")
		                    <dt class="col-sm-4">Base Price :</dt>
		                    <dd class="col-sm-8">{{$request->payment->fixed ? currency($request->payment->fixed) : currency(' 0.00')}}</dd>

		                    <dt class="col-sm-4">Time Price :</dt>
		                    <dd class="col-sm-8">{{$request->payment->time_price ? currency($request->payment->time_price) : currency(' 0.00')}}</dd>


		                    <dt class="col-sm-4">Tax Price :</dt>
		                    <dd class="col-sm-8">{{$request->payment->tax ? currency($request->payment->tax) : currency(' 0.00')}}</dd>

		                    <dt class="col-sm-4">Total Amount :</dt>
		                    <dd class="col-sm-8">
		                    	{{$request->payment->total ? currency($request->payment->total) : currency(' 0.00')}}
		                    </dd>
		                    @endif

		                    <dt class="col-sm-4">@lang('main.service') Status : </dt>
		                    <dd class="col-sm-8">
		                        {{$request->status}}
		                    </dd>

		                </dl>
		            </div>
		            <?php 
                    $map_icon = asset('asset/marker.png');
                    $static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=1000x400&maptype=terrian&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$request->s_latitude.",".$request->s_longitude."&key=".Setting::get('map_key'); ?>
			            <div class="col-md-6">
			                <div id="map" style="background-image: url({{$static_map}}) ;background-repeat: no-repeat;"></div>
			            </div>
			        </div>
		        </div>
            </div>
        </div>
    </div>

@endsection

@section('styles')
<style type="text/css">

    #map {
        height: 100%;
        min-height: 400px; 
    }

</style>
@endsection

