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

 <div class="pro-dashboard-content">
        <!-- Earning head -->
        <div class="earning-head">
            <div class="container">
                <div class="earning-element">
                     <h3 class="earning-section-tit">WayFor Pay Confirmation</h3> 
                </div>
 
                <div class="earning-element row no-margin">

<?php

$name = $user->first_name.$user->last_name;

        $merchantAccount = "freeworker_online";
        $merchantDomainName = URL::to('/');
        $returnUrl = URL::to('/').'/provider/subscription/pay';
        $orderDate = $current_date;
        $amount = $amount;
        $currency = "UAH";
        $orderTimeout = "49000";
        $productName1 = "$name Wallet Transaction";
        $productName2 = "$name Wallet Transaction";
        $productPrice1 = $amount;
        $productPrice2 = $amount;

        $string = "$merchantAccount;$merchantDomainName;$random_store_id;$orderDate;$amount;$currency;$productName1;$productName2;1;1;$productPrice1;$productPrice2";
        $key = "c3036fcf46ee9b972ba7d7fff503e4988cee2c48";
        $hash = hash_hmac("md5",$string,$key);

?>

<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">WayforPay Confirm Payment</h4>
            </div>
        </div>


  <div class="input-group full-input">
	<img  style="width:300px;height:160px;position: center;"  src="{{asset('/wayforpay.png')}}">
</div>


   <div class="row no-margin payment">
                        <form method="post" action="https://secure.wayforpay.com/pay" accept-charset="utf-8" id="easyPayAuthForm">
                        <input type="hidden" name="merchantAccount" value="{{$merchantAccount}}">
                        <input type="hidden" name="merchantDomainName" value="{{$merchantDomainName}}">
                        <input type="hidden" name="merchantSignature" value="{{$hash}}">
                        <input type="hidden" name="orderReference" value="{{$random_store_id}}">
                        <input type="hidden" name="orderDate" value="{{$orderDate}}">
                        <input type="hidden"name="amount" value="{{$amount}}">
                        <input type="hidden" name="currency" value="UAH">
                        <input type="hidden" name="orderTimeout" value="{{$orderTimeout}}">
                        <input type="hidden" name="productName[]" value="{{$productName1}}">
                       <input type="hidden" name="productName[]" value="{{$productName2}}">
                        <input type="hidden" name="productPrice[]" value="{{$productPrice1}}">
                        <input type="hidden" name="productPrice[]" value="{{$productPrice2}}">
                        <input type="hidden" name="productCount[]" value="1">
                        <input type="hidden" name="productCount[]" value="1">
                        <input type="hidden" name="defaultPaymentSystem" value="card">
                        <input type="hidden"  name="returnUrl" value="{{$returnUrl}}">
                        <button type="submit">????????????????</button>
                        </form>
            </div>
    </div>
</div>

@endsection

@section('scripts')

<script>
(function() {
  document.getElementById("easyPayAuthForm").submit();
})();
</script>

@endsection