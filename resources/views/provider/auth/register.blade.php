@extends('provider.layout.auth')

@section('content')
<div class="col-md-12">
    <a class="log-blk-btn" href="{{ url('/provider/login') }}">ALREADY REGISTERED?</a>
    <h3>Create a New Account</h3>
</div>

<div class="col-md-12">
    <form class="form-horizontal" role="form" method="POST" action="{{ url('/provider/register') }}">


          <div id="first_step">
            <div class="col-md-4">
                <input value="+38" type="text" placeholder="+38" id="country_code" name="country_code" />
            </div> 
            
            <div class="col-md-8">
                <input type="phone" autofocus id="phone_number" class="form-control" placeholder="Enter phone number" name="phone_number" value="{{ old('phone_number') }}" data-stripe="number" maxlength="10" onkeypress="return isNumberKey(event);"/>
            </div>

            <div class="col-md-12 exist-msg" style="display: none;">
                <span class="help-block">
                        <strong>Mobile number already exists!!</strong>
                </span>
            </div>

            <div class="col-md-8">
                @if ($errors->has('phone_number'))
                    <span class="help-block">
                        <strong>{{ $errors->first('phone_number') }}</strong>
                    </span>
                @endif
            </div>

            <div class="col-md-12 mobile_otp_verfication" style="display: none;">
                <input type="text" class="form-control" placeholder="@lang('user.otp')" name="otp" id="otp" value="">

                @if ($errors->has('otp'))
                    <span class="help-block">
                        <strong>{{ $errors->first('otp') }}</strong>
                    </span>
                @endif
            </div>
            <input type="hidden" id="otp_ref"  name="otp_ref" value="" />
            <input type="hidden" id="otp_phone"  name="phone" value="" />

            <div class="col-md-12" style="padding-bottom: 10px;" id="mobile_verfication">
                <input type="button" class="log-teal-btn small" onclick="smsLogin();" value="Verify Phone Number"/>
            </div>

            <div class="col-md-12 mobile_otp_verfication" style="padding-bottom: 10px;display:none" id="mobile_otp_verfication">
                <input type="button" class="log-teal-btn small" onclick="checkotp();" value="Verify Otp"/>
            </div>

  
        </div>

        {{ csrf_field() }}

    <div id="second_step" style="display: none;">
        <input id="name" type="text" class="form-control" name="first_name" value="{{ old('first_name') }}" placeholder="First Name" autofocus>

        @if ($errors->has('first_name'))
            <span class="help-block">
                <strong>{{ $errors->first('first_name') }}</strong>
            </span>
        @endif

        <input id="name" type="text" class="form-control" name="last_name" value="{{ old('last_name') }}" placeholder="Last Name">

        @if ($errors->has('last_name'))
            <span class="help-block">
                <strong>{{ $errors->first('last_name') }}</strong>
            </span>
        @endif

        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="E-Mail Address">

        @if ($errors->has('email'))
            <span class="help-block">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
        @endif

        <input id="password" type="password" class="form-control" name="password" placeholder="Password">

        @if ($errors->has('password'))
            <span class="help-block">
                <strong>{{ $errors->first('password') }}</strong>
            </span>
        @endif

        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password">

        @if ($errors->has('password_confirmation'))
            <span class="help-block">
                <strong>{{ $errors->first('password_confirmation') }}</strong>
            </span>
        @endif

        <select id="service_type" class="form-control" name="service_type">
                <option>Select ServiceType</option>
             @foreach(get_all_service_types() as $type)
                 <option value="{{$type->id}}">{{$type->name}}</option>
             @endforeach
        </select>

         @if ($errors->has('service_type'))
            <span class="help-block">
                <strong>{{ $errors->first('service_type') }}</strong>
            </span>
        @endif

        <button type="submit" class="log-teal-btn">
            Register
        </button>
    </form>
    </div>
</div>
@endsection
@section('scripts')
    <!-- <script type="text/javascript" src="{{ asset('asset/js/map.js') }}"></script> -->

    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&libraries=places&callback=initMap" async defer></script>
    <script>
        $(document).ready(function(){
            $("#submit").click(function(){
                var val = $('#subscribeNews').is(':checked');
                if(val == false){
                    alert("please read the terms and condution"); onsubmit: false      
                }
                
            });
        });
    </script>
    <script type="text/javascript">
        function disableEnterKey(e)
        {
            var key;
            if(window.e)
                key = window.e.keyCode; // IE
            else
                key = e.which; // Firefox

            if(key == 13)
                return e.preventDefault();
        }
    </script>
    <script>
    function initMap() {
        var myLatlng = new google.maps.LatLng(10.397, 100.644);
        var myOptions = {
          zoom: 1,
          center: myLatlng
        }
        var map = new google.maps.Map(document.getElementById("map"), myOptions);
        var geocoder = new google.maps.Geocoder();

        google.maps.event.addListener(map, 'click', function(event) {

          geocoder.geocode({
            'latLng': event.latLng
          }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
              if (results[0]) {
                //alert(JSON.stringify(results[0]));
                for (var i = 0; i < results.length; i++) {
                    //if (results[i].types[0] === "locality") {
                        var city = results[i].address_components[0].short_name;
                        var country = results[i].address_components[3].long_name;
                        var state = results[i].address_components[2].long_name;
                        $("#state").val(state);
                        $("#country").val(country);
                        
                   // }
                }
                
            }
        }
    });
});



    }
    </script>
 <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.3.26/jquery.form-validator.min.js"></script>
<script type="text/javascript">
    var my_otp='';
 
    function isNumberKey(evt)
    {   
        var edValue = document.getElementById("phone_number");
        var s = edValue.value;
        if (event.keyCode == 13) {
            event.preventDefault();
            if(s.length>=10){
                smsLogin();
            }
        }
        var charCode = (evt.which) ? evt.which : event.keyCode;
        if (charCode != 46 && charCode > 31 
        && (charCode < 48 || charCode > 57))
            return false;

        return true;
    }



    function smsLogin(){

        $('.exist-msg').hide();
        var countryCode = document.getElementById("country_code").value;
        var phoneNumber = document.getElementById("phone_number").value;
        $('#otp_phone').val(countryCode+''+phoneNumber);
        var csrf = $("input[name='_token']").val();;

            $.ajax({
                url: "{{url('/provider/otp')}}",
                type:'POST',
                data:{ mobile : countryCode+''+phoneNumber,'_token':csrf ,phoneonly:phoneNumber},
                success: function(data) { 

                    if($.isEmptyObject(data.error)){
                     //   my_otp=data.otp;
                        $('#otp_ref').val(data.otp);
                        $('.mobile_otp_verfication').show();
                        $('#mobile_verfication').hide();
                        $('#mobile_verfication').html("<p class='helper'> Please Wait... </p>");
                        $('#phone_number').attr('readonly',true);
                        $('#country_code').attr('readonly',true);
                        $(".print-error-msg").find("ul").html('');
                        $(".print-error-msg").find("ul").append('<li>'+data.message+'</li>');
                    }else{
                        
                        printErrorMsg(data.error);
                    }
                },
                error:function(jqXhr,status) { 
                    if(jqXhr.status === 422) {
                        $(".print-error-msg").show();
                        var errors = jqXhr.responseJSON;

                        $.each( errors , function( key, value ) { 
                            $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
                        }); 
                    } 
                }

                });
    }

    function printErrorMsg (msg) { 

        $(".print-error-msg").find("ul").html('');
        $(".print-error-msg").css('display','block');
       
        $(".print-error-msg").show();
       
            $(".print-error-msg").find("ul").append('<li><p>'+msg+'</p></li>');
        
    }


       function checkotp(){

        var my_otp = $('#otp_ref').val();
        var otp = document.getElementById("otp").value;
        if(otp){
            if(my_otp == otp){
                $(".print-error-msg").find("ul").html('');
                $('#mobile_otp_verfication').html("<p class='helper'> Please Wait... </p>");
                $('#phone_number').attr('readonly',true);
                $('#country_code').attr('readonly',true);
                $('.mobile_otp_verfication').hide();
                $('#second_step').fadeIn(400);
                $('#mobile_verfication').show().html("<p class='helper'> * Phone Number Verified </p>");
                my_otp='';
            }else{
                $(".print-error-msg").find("ul").html('');
                $(".print-error-msg").find("ul").append('<li>Otp not Matched!</li>');
            }
        }
    }



</script>
@endsection
