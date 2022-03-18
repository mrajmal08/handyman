<?php

$name = $user->first_name.$user->last_name;

        $merchantAccount = "freeworker_online";
        $merchantDomainName = URL::to('/');
        $returnUrl = URL::to('/').'/api/user/wayforpay/wallet/success';
        $orderDate = $current_date;
        $amount = $request->amount;
        $currency = "UAH";
        $orderTimeout = "49000";
        $productName1 = "$name Wallet Transaction";
        $productName2 = "$name Wallet Transaction";
        $productPrice1 = $request->amount;
        $productPrice2 = $request->amount;

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
                        <button type="submit">Оплатить</button>
                        </form>
            </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script>
(function() {
  document.getElementById("easyPayAuthForm").submit();
})();
</script>
