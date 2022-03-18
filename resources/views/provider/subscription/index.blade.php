@extends('provider.layout.app')

@section('content')
<div class="pro-dashboard-head">
    <div class="container">
        <a href="{{ route('provider.profile.index') }}" class="pro-head-link">@lang('provider.profile.profile')</a>
        <a href="{{ route('provider.documents.index') }}" class="pro-head-link">@lang('provider.profile.manage_documents')</a>
        <a href="{{ route('provider.location.index') }}" class="pro-head-link">@lang('provider.profile.update_location')</a>        
        @if(Setting::get('CARD')==1)
            <a href="{{ route('provider.cards') }}" class="pro-head-link">@lang('provider.card.list')</a>
        @endif
        <a href="{{ url('provider/subscription') }}" class="pro-head-link active">@lang('main.subscription')</a>
    </div>
</div>

<div class="pro-dashboard-content gray-bg">
    <div class="container">
        <div class="manage-docs pad30">
            @include('common.notify')
            <div class="manage-doc-content">
                <div class="manage-doc-section pad50">
                    <div class="manage-doc-section-head row no-margin">
                        <h3 class="manage-doc-tit">
                            @lang('main.subscription_plans')
                        </h3>
                    </div>

                     <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>@lang('main.plan_name')</th>
                        <th>@lang('main.amount')</th>
                        <th>@lang('main.validity')</th>
                        <th>@lang('main.description')</th>
                        <th>@lang('main.action')</th>
                    </tr>
                </thead>
                <tbody>
              
                @foreach($subscription_plans as $index => $subscription_plan)
                    <tr>
                        <input type="hidden" name="amount" value="{{ $subscription_plan->amount }}" id="amount_{{$subscription_plan->id}}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $subscription_plan->name }}</td>
                        <td>{{ $subscription_plan->amount }}</td>
                        <td>{{ $subscription_plan->validity }}</td>
                        <td>{{ $subscription_plan->description }}</td>
                        <td>
                            @if($provider->subscription_status == 'INACTIVE')
                            <form class="profile-form" action="{{url('provider/subscription/wayforpay')}}" method="POST" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <input type="hidden" name="subscription" value="{{$subscription_plan->id}}">
                            <input type='submit' class="btn btn-success btn-block" value="Subscribe">
                           <!--  <button class="btn btn-success btn-block" onclick="call('{{$subscription_plan->id}}')">Subscribe
                                </button> -->
                            </form>    
                            @else
                                @if($provider->subscription)
                                    @if($subscription_plan->id == $provider->subscription->subscription_plan_id)
                                        <span class="badge badge-success">Paid / Renewal on {{date('d-m-Y',strtotime($provider->subscription->end_date))}}</span>
                                    @endif
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>@lang('main.plan_name')</th>
                        <th>@lang('main.amount')</th>
                        <th>@lang('main.validity')</th>
                        <th>@lang('main.description')</th>
                        <th>@lang('main.action')</th>
                    </tr>
                </tfoot>
            </table>

                    <div class="manage-doc-section-content">
                        @foreach($subscription_plans as $subscription_plan)
                        <div class="manage-doc-box row no-margin border-top">
                           
                            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                                
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

@section('styles')
<link href="{{ asset('asset/css/jasny-bootstrap.min.css') }}" rel="stylesheet" type="text/css">
<style type="text/css">
    .fileinput .btn-file {
        padding:0;
        background-color: #fff;
        border: 0;
        border-radius:0!important;
    }
    .fileinput .form-control {
        border: 0;
        box-shadow : none;
        border-left:0;
        border-right:5px;
    }
    .fileinput .upload-link {
        border:0;
        border-radius: 0;
        padding:0;
    }
    .input-group-addon.btn {
        background: #fff;
        border: 1px solid #37b38b;
        border-radius: 0; 
        padding: 10px;
        height: 40px;
        line-height: 20px;
    }
    .fileinput .fileinput-filename {
        font-size: 10px;
    }
    .fileinput .btn-submit {
        padding: 0;
    }
    .fileinput button {
        background-color: white;
        border: 0;
        padding: 10px;
    }
</style>
@endsection

@section('scripts')
<script type="text/javascript" src="{{ asset('asset/js/jasny-bootstrap.min.js') }}"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>



function call(plan) {
   var amount = $('#amount_'+plan).val();
   var email = $('#email').val();
   var name = $('#name').val();
   var currency = $('#currency').val();

  
    // alert(name);

    var options = {
    "key": "rzp_test_hIWWlUbTjDeRES",
    "amount": amount*100,
    "currency": currency,
    "name": "Merchant Name",
    "description": "Purchase Description",
    "image": "{{ asset('asset/img/site_logo.png') }}",
    "handler": function (response){
// console.log();
        $.ajax({
            type: "POST",
            url: "{{url('/provider/subscription')}}",
            data:{ payment_id: response.razorpay_payment_id , 'subscription' : plan },           
            dataType: "json",
            success: function(data) {
                //location.reload('data.redirect');
            }
        });    

    },
    "prefill": {
        "name":name ,
        "email": email,
        "currency": currency
    },
    "notes": {
        "address": ""
    },
    "theme": {
        "color": "#e86609"
    }
};
var rzp1 = new Razorpay(options);
rzp1.open();


   
}

</script>
@endsection
