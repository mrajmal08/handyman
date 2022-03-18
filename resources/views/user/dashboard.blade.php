@extends('user.layout.base')

@section('title', 'Dashboard ')

@section('content')
<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">Select Services</h4>
            </div>
        </div>
        @include('common.notify')
        <div class="services row no-margin">
            @foreach($services as $service)
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="services-sel-box">
                    <a href="{{url('dashboard')}}?service={{$service->id}}" class="sel-ser-link">
                        <div class="sel-ser-img bg-img" style="background-image: url({{img($service->image)}});"></div>
                        <h3 class="sel-ser-tit">{{$service->name}}</h3>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection