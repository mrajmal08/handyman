@extends('provider.layout.app')

@section('content')
<div class="pro-dashboard-head">
    <div class="container">
        <a href="{{ route('provider.profile.index') }}" class="pro-head-link">@lang('provider.profile.profile')</a>
        <a href="{{ route('provider.documents.index') }}" class="pro-head-link">@lang('provider.profile.manage_documents')</a>
        <a href="{{ route('provider.location.index') }}" class="pro-head-link">@lang('provider.profile.update_location')</a>
        <a href="#" class="pro-head-link active">@lang('provider.profile.wallet_transaction')</a>
        @if(config('constants.card')==1)
            <a href="{{ route('provider.cards') }}" class="pro-head-link">@lang('provider.card.list')</a>
        @endif
        <a href="{{ route('provider.transfer') }}" class="pro-head-link">@lang('provider.profile.transfer')</a>
        @if(config('constants.referral')==1)
            <a href="{{ route('provider.referral') }}" class="pro-head-link">@lang('provider.profile.refer_friend')</a>
        @endif
         <a href="{{ url('provider/subscription') }}" class="pro-head-link">Subscription</a>
    </div>
</div>

<div class="pro-dashboard-content gray-bg">
    <div class="container">
        <div class="manage-docs pad30">
            <div class="manage-doc-content">
                <div class="manage-doc-section pad50">
                    <!-- <div class="manage-doc-section-head row no-margin">
                        <h3 class="manage-doc-tit">
                            @lang('provider.profile.wallet_transaction')
                            (@lang('provider.current_balance') : {{currency($wallet_balance)}})
                        </h3>
                    </div> -->
                    @include('common.notify')
                    <div class="row no-margin">
                    <form action="{{url('/provider/add/money')}}" id="adyen-encrypted-form" method="POST">
                    {{ csrf_field() }}
                        <div class="col-md-6">
                             
                            <div class="wallet">
                                <h4 class="amount">
                                    <span class="price">{{currency(Auth::user()->wallet_balance)}}</span>
                                    <span class="txt">@lang('user.in_your_wallet')</span>
                                </h4>
                            </div>                                                               

                        </div>
                        <div class="col-md-6">
                            
                            <h6><strong>@lang('user.add_money')</strong></h6>

                            <select class="form-control" autocomplete="off" name="payment_mode" onchange="card(this.value);">
                              <option value="">Select</option>
                              @if(Config::get('constants.card') == 1)
                              @if($cards->count() > 0)
                                <option value="CARD">CARD</option>
                              @endif
                              @if(Config::get('constants.braintree') == 1)
                              <option value="BRAINTREE">BRAINTREE</option>
                              @endif
                              @endif
                              @if(Config::get('constants.payumoney') == 1)
                              <option value="PAYUMONEY">PAYUMONEY</option>
                              @endif
                              @if(Config::get('constants.adyen') == 1)
                              <option value="ADYEN">ADYEN</option>
                              @endif
                              @if(Config::get('constants.paypal') == 1)
                              <option value="PAYPAL">PAYPAL</option>
                              @endif
                              @if(Config::get('constants.paytm') == 1)
                              <option value="PAYTM">PAYTM</option>
                              @endif
                            </select>
                            <br>
                            
                            @if(Config::get('constants.card') == 1)
                            <select style="display: none;" class="form-control" name="card_id" id="card_id">
                              @foreach($cards as $card)
                                <option @if($card->is_default == 1) selected @endif value="{{$card->card_id}}">{{$card->brand}} **** **** **** {{$card->last_four}}</option>
                              @endforeach
                            </select>
                            @endif

                            @if(Config::get('constants.braintree') == 1)
                                <div style="display: none;" id="braintree">
                                    <div id="dropin-container"></div>
                                </div>
                            @endif

                            <br>
                            @if(Config::get('constants.braintree') == 1)
                            <input type="hidden" name="braintree_nonce" value="" />
                            @endif
                            <input type="hidden" name="user_type" value="provider" />
                            <div class="input-group full-input">
                                <input type="number" class="form-control" name="amount" placeholder="@lang('user.enter_amount')" >
                            </div>

                            @if(Config::get('constants.adyen') == 1)
                   
                  <div class="modal-body">
                  <div class="row no-margin" id="adeyn-payment" style="display: none;">
                  <div class="form-group col-md-12 col-sm-12">
                      <label>@lang('user.card.fullname')</label>
                      <input data-encrypted-name="holderName" autocomplete="off" required type="text" class="form-control" placeholder="@lang('user.card.fullname')">
                  </div>
                  <div class="form-group col-md-12 col-sm-12">
                      <label>@lang('user.card.card_no')</label>
                       <input type="text" size="20" maxlength="16" autocomplete="off" placeholder="@lang('user.card.card_no')" class="form-control" data-encrypted-name="number" />
                  </div>
                  <div class="form-group col-md-4 col-sm-12">
                      <label>@lang('user.card.month')</label>
                      <input type="text"  maxlength="2" required autocomplete="off" class="form-control" data-encrypted-name="expiryMonth" placeholder="MM">
                  </div>
                  <div class="form-group col-md-4 col-sm-12">
                      <label>@lang('user.card.year')</label>
                      <input type="text" maxlength="4" required autocomplete="off" data-encrypted-name="expiryYear" class="form-control" placeholder="YYYY">
                  </div>
                  <div class="form-group col-md-4 col-sm-12">
                      <label>@lang('user.card.cvv')</label>
                      <input type="text" data-encrypted-name="cvc"  required autocomplete="off" maxlength="4" class="form-control" placeholder="@lang('user.card.cvv')">
                  </div>
                   <input type="hidden" id="adyen-encrypted-form-expiry-generationtime" value="generate-this-server-side" data-encrypted-name="generationtime" />
                  </div>
                  </div>

                   <button vslue="Pay" type="submit" class="full-primary-btn fare-btn adyen_button">Adyen Payment</button> 
                 @endif
                 
                 

                  <button type="submit" id="submit-button" class="full-primary-btn fare-btn others">@lang('user.add_money')</button> 
             

                            
                          

                        </div>
                    </form>

                </div>

                   
                     <div class="manage-doc-section-content">
                     <div class="tab-content list-content">
                      <div class="list-view pad30 ">

                            <table class="earning-table table table-responsive">
                                <thead>
                                    <tr>
                                        <th>@lang('provider.sno')</th>
                                        <th>@lang('provider.transaction_ref')</th>
                                        <th>@lang('provider.datetime')</th>
                                       <!--  <th>@lang('provider.transaction_desc')</th>
                                        <th>@lang('provider.status')</th> -->
                                        <th>@lang('provider.amount')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php($page = ($pagination->currentPage-1)*$pagination->perPage)
                               @foreach($wallet_transation as $index=>$wallet)
                               @php($page++)
                                    <tr>
                                        <td>{{$page}}</td>
                                        <td><a href="javascript:void(0);" class="new-pro-link trdclass" data-toggle="trdetails" title="Transaction Details" data-content="" data-alias="#wallet_{{$wallet->transaction_alias}}">{{$wallet->transaction_alias}}</a></td>
                                        <td>{{$wallet->transactions[0]->created_at->diffForHumans()}}</td>
                                       <!--  <td>{{$wallet->transaction_desc}}</td> -->
                                       <!--  <td>@if($wallet->type == 'C') @lang('provider.credit') @else @lang('provider.debit') @endif</td> -->
                                        <td>{{currency($wallet->amount)}}
                                        </td>
                                        <td style="display: none;" id="wallet_{{$wallet->transaction_alias}}">
                                            <table class="table table-responsive">
                                                <thead>
                                                    <tr>
                                                        <th>Description</th><th>Type</th><th>Amount</th>
                                                    </tr>
                                                <tbody>
                                                    @foreach($wallet->transactions as $index=>$transactions)
                                                        <tr>
                                                            <td>{{$transactions->transaction_desc}}</td>
                                                            <td>@if($transactions->type=='C') Credit @else Debit @endif</td>
                                                            <td>@if($transactions->type=='C')<span style="color: green"> {{currency($transactions->amount)}}</span>@else<span style="color: red"> {{currency($transactions->amount)}}</span>@endif</td>
                                                        </tr>
                                                    @endforeach    
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                          
                            {{ $wallet_transation->links() }}
                        </div>
                     </div>
                     </div>
               
                </div>
            </div>
        </div>
    </div>

</div>
<style type="text/css">
    .popover{
        max-width: 500px !important;
    }
</style>
@endsection

@section('scripts')
@if(Config::get('constants.braintree') == 1)
<script src="https://js.braintreegateway.com/web/dropin/1.14.1/js/dropin.min.js"></script>

<script>
    var button = document.querySelector('#submit-button');
    var form = document.querySelector('#add_money');
    braintree.dropin.create({
      authorization: '{{$clientToken}}',
      container: '#dropin-container',
      //Here you can hide paypal
      paypal: {
        flow: 'vault'
      }
    }, function (createErr, instance) {
      button.addEventListener('click', function (e) {
        e.preventDefault();
        if(document.querySelector('select[name="payment_mode"]').value == "BRAINTREE") {
            instance.requestPaymentMethod(function (requestPaymentMethodErr, payload) {
               document.querySelector('input[name="braintree_nonce"]').value = payload.nonce;
               console.log(payload.nonce);
               form.submit();
          });
          } else {
            form.submit();
          }
        
      });
    });
</script>
@endif


@if(Config::get('constants.adyen') == 1)

<script type="text/javascript" src="{{asset('js/adyen.encrypt.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/require.js')}}"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript">
document.getElementById('adyen-encrypted-form-expiry-generationtime').value = new Date().toISOString();
var form    = document.getElementById('adyen-encrypted-form');
console.log(form);
var key     =   "10001|A49D22228CDA7D6C844377CB7028E7DAAC69AB3B799A781CA21B9C2D52EAD6FFC802D5EC09EEB1BA22EEFEDC62F0143D562FCB7467E6B56198EA27FA6A8A94F9281E15939F5A5E32D5F24A427968405512DF11CA812DBA1B1653DA1D1EA25D8721D294B969326EB0C09C70B89D1F97BF5D47A23AB2092BB3A30FDA8CD8C888CA91EB8957CD17EF02916C0FED1976B50D165CE49B01FA0A9E39305997706A90CF9EA752163B5DAB66B1AA9B5A2F32ED4BE7B947E7AF657B7D0F06BBCBF9BFF12F2E89FFF5CFB444FB7D16993163D71EB72B573E9A9B483349F19390E74DBC0CD283E9F28F3070E5DD85801F855969E15B76C874EDC037A01FCDDC02A24B302E8F"; 
var options = {};
adyen.encrypt.createEncryptedForm( form, key, options);


</script>
@endif



<script>
var request=0; 

  $('.adyen_button').hide();
    @if(Config::get('constants.card') == 1)
        card('CARD');
    @endif

    function card(value){
        $('#card_id, #braintree').fadeOut(300);
        if(value == 'CARD'){
            $('#card_id').fadeIn(300);
        }else if(value == 'BRAINTREE'){
            $('#braintree').fadeIn(300);
        }else if(value == 'ADYEN'){
            $('#adeyn-payment').fadeIn(300);
            $("#adyen-encrypted-form").attr('id', 'add_money');
            $('.others').hide();
            $('.adyen_button').show();

        }
    }

$(document).ready(function(){
    $("[data-toggle=trdetails]").popover({
        html : true,
        content: function() {
          $('[data-toggle=trdetails]').not(this).popover('hide');  
          var content = $(this).attr("data-alias");
          console.log(content);
          return $(content).html();
        },
        
    });   
});  

</script>
@endsection
