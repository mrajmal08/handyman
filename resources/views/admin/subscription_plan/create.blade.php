@extends('admin.layout.base')

@section('title', 'Subscription Plan')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ url('admin/subscription_plans/') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

            <h5 style="margin-bottom: 2em;">Subscription Plan</h5>

            <form class="form-horizontal" action="{{url('admin/subscription_plans/create')}}" method="POST" enctype="multipart/form-data" role="form">
                {{ csrf_field() }}

             
                <div class="form-group row">
                    <label for="name" class="col-xs-12 col-form-label">Plan Name</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="name" placeholder="Plan Name">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="provider_name" class="col-xs-12 col-form-label">Plan Amount</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('amount') }}" name="amount" required id="amount" placeholder="Amount">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="name" class="col-xs-12 col-form-label">Plan Validity</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('validity') }}" name="validity" required id="name" placeholder="Plan Validity">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="name" class="col-xs-12 col-form-label">Description</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('description') }}" name="description" required id="description" placeholder="Plan Description">
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="{{url('admin/subscription_plans/')}}" class="btn btn-danger btn-block">@lang('admin.cancel')</a>
                    </div>
                    <div class="col-xs-12 col-sm-6 offset-md-6 col-md-3">
                        <button type="submit" class="btn btn-primary btn-block">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

