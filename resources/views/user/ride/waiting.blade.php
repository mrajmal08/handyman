@extends('user.layout.base')

@section('title', 'On Ride')

@section('content')
<div class="col-md-9">
    <div class="dash-content">
        @include('common.notify')
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title" id="ride_status"></h4>
            </div>
        </div>
        
        <div class="row no-margin">
                <div class="col-md-6" id="container" >
                    <p>Loading...</p>                             
                </div>

                <div class="col-md-6">
                    <dl class="dl-horizontal left-right">
                        <dt>@lang('user.request_id')</dt>
                        <dd>{{$request->id}}</dd>
                        <dt>@lang('user.time')</dt>
                        <dd>{{date('d-m-Y H:i A',strtotime($request->assigned_at))}}</dd>
                    </dl> 
                    <div class="user-request-map">
                        <div class="from-to row no-margin">
                            <div class="from">
                                <h5>SERVICE LOCATION</h5>
                                <p>{{$request->s_address}}</p>
                            </div>
                           
                            <div class="type">
                                <h5>SERVICE TYPE</h5>
                                <p>@if($request->service_type != null) {{$request->service_type->name}}  @endif</p>
                            </div>
                        </div>
                        <?php 
                            $map_icon = asset('asset/marker.png');
                            $static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=600x450&maptype=roadmap&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$request->s_latitude.",".$request->s_longitude."&markers=icon:".$map_icon."%7C".$request->d_latitude.",".$request->d_longitude."&path=color:0x191919|weight:8|".$request->s_latitude.",".$request->s_longitude."|".$request->d_latitude.",".$request->d_longitude."&key=".Setting::get('map_key'); ?>
                            <div class="map-static" style="background-image: url({{$static_map}});"></div>                               
                    </div>                          
                </div>
        </div>
       <!--  @if(in_array($request->status,array('ACCEPTED','STARTED','ARRIVED')))
            <div class="col-md-12">
                <h2 class="text-center">Chat with Provider</h2>
                <div class="row">
                    <div class="panel-chat well m-n chat-box" id="chat-box" style="overflow-y: scroll; height: 200px;">
                    
                    </div>
                    <div class="p-md">
                        <div class="input-group">
                            <input placeholder="Enter your message here" class="form-control" type="text" id="chat-input">
                            <span class="input-group-btn">
                                <button type="button" id="chat-send" class="btn btn-default"><i class="fa fa-arrow-right"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
             </div>
        @endif -->
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript" src="{{asset('asset/js/rating.js')}}"></script>    
<script type="text/javascript">
    $('.rating').rating();
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox();
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/react/0.13.3/react.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/react/0.13.3/JSXTransformer.js"></script>

<script type="text/jsx">
	function interval(){
	        clearInterval(this.updateInterval);
	    }

    var MainComponent = React.createClass({
        getInitialState: function () {
                return {data: [], currency : "{{Setting::get('currency')}}", base_url : "{{asset('storage')}}/"};
            },
        componentDidMount: function(){
            $.ajax({
              url: "{{url('status')}}",
              type: "GET"})
              .done(function(response){

                    this.setState({
                        data:response.data[0]
                    });

                }.bind(this));

                this.updateInterval = setInterval(this.checkRequest, 5000);

            	interval = interval.bind(this);
        },
        checkRequest : function(){
            $.ajax({
              url: "{{url('status')}}",
              type: "GET"})
              .done(function(response){
                    this.setState({
                        data:response.data[0]
                    });

                }.bind(this));
        },
        render: function(){
            return (
                <div>
                    <SwitchState checkState={this.state.data} currency={this.state.currency} 
                    base_url={this.state.base_url} />
                </div>
            );
        }
    });

    var SwitchState = React.createClass({

        componentDidMount: function() {
            this.changeLabel;
        },

        changeLabel : function(){
            if(this.props.checkState == undefined){
                window.location.reload();
            }else if(this.props.checkState != ""){
                if(this.props.checkState.status == 'SEARCHING'){
                    $("#ride_status").text("@lang('user.ride.finding_driver')");
                }else if(this.props.checkState.status == 'STARTED'){
                    var provider_name = this.props.checkState.provider.first_name;
                    $("#ride_status").text(provider_name+" @lang('user.ride.accepted_ride')");
                }else if(this.props.checkState.status == 'ARRIVED'){
                    var provider_name = this.props.checkState.provider.first_name;
                    $("#ride_status").text(provider_name+" @lang('user.ride.arrived_ride')");
                }else if(this.props.checkState.status == 'PICKEDUP'){
                    $("#ride_status").text("@lang('user.ride.onride')");
                }else if(this.props.checkState.status == 'DROPPED'){
                    $("#ride_status").text("@lang('user.ride.waiting_payment')");
                }else if(this.props.checkState.status == 'COMPLETED'){
                    var provider_name = this.props.checkState.provider.first_name;
                    $("#ride_status").text("@lang('user.ride.rate_and_review') " +provider_name );
                }
                setTimeout(function(){
                    $('.rating').rating();
                },400);
            }else{
                $("#ride_status").text('Text will appear here');
            }
        },
        render: function(){

            if(this.props.checkState != ""){

                this.changeLabel();
                if(this.props.checkState.status == 'SEARCHING'){
                    return (
                        <div>
                            <Searching checkState={this.props.checkState} />
                        </div>
                    );
                }else if(this.props.checkState.status == 'STARTED'){
                    return (
                        <div>
                            <Accepted checkState={this.props.checkState} />
                        </div>
                    );
                }else if(this.props.checkState.status == 'ARRIVED'){
                    return (
                        <div>
                            <Arrived checkState={this.props.checkState}/>
                        </div>
                    );
                }else if(this.props.checkState.status == 'PICKEDUP'){
                    return (
                        <div>
                            <Pickedup checkState={this.props.checkState} base_url={this.props.base_url} />
                        </div>
                    );
                }else if((this.props.checkState.status == 'DROPPED' || this.props.checkState.status == 'COMPLETED') && this.props.checkState.payment_mode == 'CASH' && this.props.checkState.paid == 0){
                    return (
                        <div>
                            <DroppedAndCash checkState={this.props.checkState} currency={this.props.currency} base_url={this.props.base_url} />
                        </div>
                    );
                }else if((this.props.checkState.status == 'DROPPED' || this.props.checkState.status == 'COMPLETED') && this.props.checkState.payment_mode == 'CARD' && this.props.checkState.paid == 0){
                    return (
                        <div>
                            <DroppedAndCard checkState={this.props.checkState} currency={this.props.currency} base_url={this.props.base_url} />
                        </div>
                    );
                }else if((this.props.checkState.status == 'DROPPED' || this.props.checkState.status == 'COMPLETED') && this.props.checkState.payment_mode == 'WAYFORPAY' && this.props.checkState.paid == 0){
                    return (
                        <div>
                            <DroppedAndWayforpay checkState={this.props.checkState} currency={this.props.currency} base_url={this.props.base_url} />
                        </div>
                    );
                }else if(this.props.checkState.status == 'COMPLETED'){
                    return (
                        <div>
                            <Review checkState={this.props.checkState} />
                        </div>
                    );
                }
            }else{
                return ( 
                    <p></p>
                 );
            }
        }
    });

    var Searching = React.createClass({
        render: function(){
            return (
                <form action="{{url('cancel/ride')}}" method="POST">
                    {{ csrf_field() }}</input>
                    <input type="hidden" name="request_id" value={this.props.checkState.id} />
                    <div className="status">
                        <h6>@lang('user.status')</h6>
                        <p>@lang('user.ride.finding_driver')</p>
                    </div>

                    <button type="submit" className="full-primary-btn fare-btn">@lang('user.ride.cancel_request')</button> 
                </form>
            );
        }
    });

    var Accepted = React.createClass({
        render: function(){
            return (
                <form action="{{url('cancel/ride')}}" method="POST">
                    {{ csrf_field() }}</input>
                <input type="hidden" name="request_id" value={this.props.checkState.id} />
                    <div className="status">
                        <h6>@lang('user.status')</h6>
                        <p>@lang('user.ride.accepted_ride')</p>
                    </div>
                    <button type="submit" className="full-primary-btn fare-btn">@lang('user.ride.cancel_request')</button> 
                    <br/>
                        <h5><strong>@lang('user.ride.ride_details')</strong></h5>
                    <div className="driver-details">
                        <dl className="dl-horizontal left-right">
                            <dt>@lang('user.driver_name')</dt>
                            <dd>{this.props.checkState.provider.first_name} {this.props.checkState.provider.last_name}</dd>
                            <dt>@lang('user.driver_rating')</dt>
                          
                            <dd>
                                <div className="rating-outer">
                                    <input type="hidden" value={this.props.checkState.provider.rating} name="rating" className="rating"/>
                                </div>
                            </dd>
                            <dt>@lang('user.payment_mode')</dt>
                            <dd>{this.props.checkState.payment_mode}</dd>
                            <dt>@lang('user.otp')</dt>
                            <dd>{this.props.checkState.otp}</dd>
                        </dl> 
                    </div>

                </form>
            );
        }
    });

    var Arrived = React.createClass({
        render: function(){
            return (
                <form action="{{url('cancel/ride')}}" method="POST">
                    {{ csrf_field() }}</input>
                <input type="hidden" name="request_id" value={this.props.checkState.id} />
                    <div className="status">
                        <h6>@lang('user.status')</h6>
                        <p>@lang('user.ride.arrived_ride')</p>
                    </div>
                    <button type="submit" className="full-primary-btn fare-btn">@lang('user.ride.cancel_request')</button> 
                    <br/>
                        <h5><strong>@lang('user.ride.ride_details')</strong></h5>
                    <div className="driver-details">
                        <dl className="dl-horizontal left-right">
                            <dt>@lang('user.driver_name')</dt>
                            <dd>{this.props.checkState.provider.first_name} {this.props.checkState.provider.last_name}</dd>
                            <dt>@lang('user.driver_rating')</dt>
                          
                            <dd>
                                <div className="rating-outer">
                                    <input type="hidden" value={this.props.checkState.provider.rating} name="rating" className="rating"/>
                                </div>
                            </dd>
                            <dt>@lang('user.payment_mode')</dt>
                            <dd>{this.props.checkState.payment_mode}</dd>
                            <dt>@lang('user.otp')</dt>
                            <dd>{this.props.checkState.otp}</dd>
                        </dl> 
                    </div>
                </form>
            );
        }
    });

    var Pickedup = React.createClass({
        render: function(){
            return (
            <div>
                <div className="status">
                    <h6>@lang('user.status')</h6>
                    <p>@lang('user.ride.onride')</p>
                </div>
                <br/>
                    <h5><strong>@lang('user.ride.ride_details')</strong></h5>
                <div className="driver-details">
                    <dl className="dl-horizontal left-right">
                        <dt>@lang('user.driver_name')</dt>
                        <dd>{this.props.checkState.provider.first_name} {this.props.checkState.provider.last_name}</dd>
                                                    
                        <dt>@lang('user.driver_rating')</dt>
                            <dd>
                                <div className="rating-outer">
                                    <input type="hidden" value={this.props.checkState.provider.rating} name="rating" className="rating"/>
                                </div>
                            </dd>
                        <dt>@lang('user.payment_mode')</dt>
                        <dd>{this.props.checkState.payment_mode}</dd>
                        
                        <a href={this.props.base_url + this.props.checkState.before_image} data-toggle="lightbox" data-title="@lang('user.before_image')" data-footer={this.props.checkState.before_comment}>
                            
                            <img src={this.props.base_url + this.props.checkState.before_image} className="before-img img-fluid"/>
                        </a>

                    </dl> 
                </div>
            </div>
            );
        }
    });

    var DroppedAndCash = React.createClass({

        render: function(){
            return (
            <div>
                <div className="status">
                    <h6>@lang('user.status')</h6>
                    <p>@lang('user.ride.dropped_ride')</p>
                </div>
                <br/>
                    <h5><strong>@lang('user.ride.ride_details')</strong></h5>
                    <dl className="dl-horizontal left-right">
                        <dt>@lang('user.driver_name')</dt>
                        <dd>{this.props.checkState.provider.first_name} {this.props.checkState.provider.last_name}</dd>
                        
                        <dt>@lang('user.driver_rating')</dt>
                        <dd>
                            <div className="rating-outer">
                                <input type="hidden" value={this.props.checkState.provider.rating} name="rating" className="rating"/>
                            </div>
                        </dd>
                        <dt>@lang('user.payment_mode')</dt>
                        <dd>{this.props.checkState.payment_mode}</dd>


                        <a href={this.props.base_url + this.props.checkState.before_image} data-toggle="lightbox" data-title="@lang('user.before_image')" data-footer={this.props.checkState.before_comment}>
                            
                            <img src={this.props.base_url + this.props.checkState.before_image} className="before-img img-fluid"/>
                        </a>

                        <a href={this.props.base_url + this.props.checkState.after_image} data-toggle="lightbox" data-title="@lang('user.after_image')" data-footer={this.props.checkState.after_comment}>
                            
                            <img src={this.props.base_url + this.props.checkState.after_image} className="before-img img-fluid"/>
                        </a>
                           
                    </dl>
                    <h5><strong>@lang('user.ride.invoice')</strong></h5>
                    <dl className="dl-horizontal left-right">
                        <dt>@lang('user.ride.base_price')</dt>
                        <dd>{this.props.currency}{this.props.checkState.payment.fixed}</dd>
                        <dt>@lang('user.ride.tax_price')</dt>
                        <dd>{this.props.currency}{this.props.checkState.payment.tax}</dd>
                        <dt>@lang('user.ride.distance_price')</dt>
                        <dd>{this.props.currency}{this.props.checkState.payment.time_price}</dd>
                        {this.props.checkState.use_wallet ?
                            <span>
                            <dt>@lang('user.ride.detection_wallet')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.wallet}</dd>  
                            </span>
                        : ''
                        }
                        {this.props.checkState.payment.discount ?
                            <span>
                            <dt>@lang('user.ride.promotion_applied')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.discount}</dd>  
                            </span>
                        : ''
                        }
                        <dt>@lang('user.ride.total')</dt>
                        <dd>{this.props.currency}{this.props.checkState.payment.total}</dd> 
                        <dt className="big">@lang('user.ride.amount_paid')</dt>
                        <dd className="big">{this.props.currency}{this.props.checkState.payment.total}</dd>
                    </dl>
            </div>
            );
        }
    });

    var DroppedAndCard = React.createClass({

        render: function(){
            return (
            <div>
                <form method="POST" action="{{url('/payment')}}">
                    {{ csrf_field() }}</input>
                <div className="status">
                    <h6>@lang('user.status')</h6>
                    <p>@lang('user.ride.dropped_ride')</p>
                </div>
                    <br/>
                    <h5><strong>@lang('user.ride.ride_details')</strong></h5>
                    <dl className="dl-horizontal left-right">
                        <dt>@lang('user.driver_name')</dt>
                        <dd>{this.props.checkState.provider.first_name} {this.props.checkState.provider.last_name}</dd>
                        
                        <dt>@lang('user.driver_rating')</dt>
                        <dd>
                            <div className="rating-outer">
                                <input type="hidden" value={this.props.checkState.provider.rating} name="rating" className="rating"/>
                            </div>
                        </dd>
                        <dt>@lang('user.payment_mode')</dt>
                        <dd>{this.props.checkState.payment_mode}</dd>

                        <a href={this.props.base_url + this.props.checkState.before_image} data-toggle="lightbox" data-title="@lang('user.before_image')" data-footer={this.props.checkState.before_comment}>
                            
                            <img src={this.props.base_url + this.props.checkState.before_image} className="before-img img-fluid"/>
                        </a>

                        <a href={this.props.base_url + this.props.checkState.after_image} data-toggle="lightbox" data-title="@lang('user.after_image')" data-footer={this.props.checkState.after_comment}>
                            
                            <img src={this.props.base_url + this.props.checkState.after_image} className="before-img img-fluid"/>
                        </a>
                    </dl>
                    <h5><strong>@lang('user.ride.invoice')</strong></h5>
                    <input type="hidden" name="request_id" value={this.props.checkState.id} />
                    <dl className="dl-horizontal left-right">
                       <dt>@lang('user.ride.base_price')</dt>
                        <dd>{this.props.currency}{this.props.checkState.payment.fixed}</dd>
                        <dt>@lang('user.ride.tax_price')</dt>
                        <dd>{this.props.currency}{this.props.checkState.payment.tax}</dd>
                        <dt>@lang('user.ride.distance_price')</dt>
                        <dd>{this.props.currency}{this.props.checkState.payment.time_price}</dd>
                        
                        <dt>@lang('user.ride.total')</dt>
                        {this.props.checkState.use_wallet ?
                            <span>
                            <dt>@lang('user.ride.detection_wallet')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.wallet}</dd>  
                            </span>
                        : ''
                        }
                        {this.props.checkState.payment.discount ?
                            <span>
                            <dt>@lang('user.ride.promotion_applied')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.discount}</dd>  
                            </span>
                        : ''
                        }
                        <dd>{this.props.currency}{this.props.checkState.payment.total}</dd> 
                        <dt className="big">@lang('user.ride.amount_paid')</dt>
                        <dd className="big">{this.props.currency}{this.props.checkState.payment.total}</dd>
                    </dl>
                    <button type="submit" className="full-primary-btn fare-btn">CONTINUE TO PAY</button>   
                </form>
            </div>
            );
        }
    });

     var DroppedAndWayforpay = React.createClass({

        render: function(){
            return (
            <div>
                <form method="POST" action="{{url('/wayforpay/payment')}}">
                    {{ csrf_field() }}</input>
                <div className="status">
                    <h6>@lang('user.status')</h6>
                    <p>@lang('user.ride.dropped_ride')</p>
                </div>
                    <br/>
                    <h5><strong>@lang('user.ride.ride_details')</strong></h5>
                    <dl className="dl-horizontal left-right">
                        <dt>@lang('user.driver_name')</dt>
                        <dd>{this.props.checkState.provider.first_name} {this.props.checkState.provider.last_name}</dd>
                        
                        <dt>@lang('user.driver_rating')</dt>
                        <dd>
                            <div className="rating-outer">
                                <input type="hidden" value={this.props.checkState.provider.rating} name="rating" className="rating"/>
                            </div>
                        </dd>
                        <dt>@lang('user.payment_mode')</dt>
                        <dd>{this.props.checkState.payment_mode}</dd>

                        <a href={this.props.base_url + this.props.checkState.before_image} data-toggle="lightbox" data-title="@lang('user.before_image')" data-footer={this.props.checkState.before_comment}>
                            
                            <img src={this.props.base_url + this.props.checkState.before_image} className="before-img img-fluid"/>
                        </a>

                        <a href={this.props.base_url + this.props.checkState.after_image} data-toggle="lightbox" data-title="@lang('user.after_image')" data-footer={this.props.checkState.after_comment}>
                            
                            <img src={this.props.base_url + this.props.checkState.after_image} className="before-img img-fluid"/>
                        </a>
                    </dl>
                    <h5><strong>@lang('user.ride.invoice')</strong></h5>
                    <input type="hidden" name="request_id" value={this.props.checkState.id} />
                    <dl className="dl-horizontal left-right">
                       <dt>@lang('user.ride.base_price')</dt>
                        <dd>{this.props.currency}{this.props.checkState.payment.fixed}</dd>
                        <dt>@lang('user.ride.tax_price')</dt>
                        <dd>{this.props.currency}{this.props.checkState.payment.tax}</dd>
                        <dt>@lang('user.ride.distance_price')</dt>
                        <dd>{this.props.currency}{this.props.checkState.payment.time_price}</dd>
                        
                        <dt>@lang('user.ride.total')</dt>
                        {this.props.checkState.use_wallet ?
                            <span>
                            <dt>@lang('user.ride.detection_wallet')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.wallet}</dd>  
                            </span>
                        : ''
                        }
                        {this.props.checkState.payment.discount ?
                            <span>
                            <dt>@lang('user.ride.promotion_applied')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.discount}</dd>  
                            </span>
                        : ''
                        }
                        <dd>{this.props.currency}{this.props.checkState.payment.total}</dd> 
                        <dt className="big">@lang('user.ride.amount_paid')</dt>
                        <dd className="big">{this.props.currency}{this.props.checkState.payment.total}</dd>
                    </dl>
                    <button type="submit" className="full-primary-btn fare-btn">CONTINUE TO PAY</button>   
                </form>
            </div>
            );
        }
    });

    var Review = React.createClass({
        render: function(){
        interval();
            return (
            <form method="POST" action="{{url('/rate')}}">
            {{ csrf_field() }}</input>
                <div className="rate-review">
                    <label>@lang('user.ride.rating')</label>
                    <div className="rating-outer">
                        <input type="hidden" value="1" name="rating" className="rating"/>
                    </div>
                    <input type="hidden" name="request_id" value={this.props.checkState.id} />
                    <label>@lang('user.ride.comment')</label>
                    <textarea className="form-control" name="comment" placeholder="Write Comment"></textarea>
                </div>
                <button type="submit" className="full-primary-btn fare-btn">SUBMIT</button>   
            </form>
            );
        }
    });

    React.render(<MainComponent/>,document.getElementById("container"));
</script>

@if(in_array($request->status,array('ACCEPTED','STARTED','ARRIVED','PICKEDUP','DROPPED','PAYMENT')))
<script src="https://cdn.socket.io/socket.io-1.4.5.js"></script>
<script src=https://cdn.pubnub.com/sdk/javascript/pubnub.4.0.11.min.js></script>
<script type="text/javascript">
    var defaultImage = "{{ asset('user_default.png') }}";
    var chatBox = document.getElementById('chat-box');
    var chatInput = document.getElementById('chat-input');
    var chatSend = document.getElementById('chat-send');

    var messageTemplate = function(data) {
        var message = document.createElement('div');
        var messageContact = document.createElement('div');
        var messageText = document.createElement('p');

        //messageText.className = "chat-text";
        messageText.innerHTML = data.message;

        if(data.type == 'up') {
            message.className = "row m-0";
            messageContact.className = "chat-right";
            messageContact.appendChild(messageText);
            message.appendChild(messageContact);
            //messageContact.innerHTML = '<img src="' + socketClient.user_picture + '">';
        } else {
            message.className = "chat-left";
            message.appendChild(messageText);
            //messageContact.innerHTML = '<img src="' + socketClient.provider_picture + '">';
        }
        
        return message;
    }

    chatSockets = function () {
        // this.socket = undefined;
        this.channel = '{{ $request->id }}';
        //this.provider_picture =  "{{ $request->provider?($request->provider->avatar?\Storage::url($request->provider->avatar):asset('user_default.png')):asset('user_default.png') }}";
        //this.user_picture = "{{ $request->user?($request->user->picture?\Storage::url($request->user->picture):asset('user_default.png')):asset('user_default.png') }}";
    }

    chatSockets.prototype.initialize = function() {
        // this.socket = io('{{ env("SOCKET_SERVER") }}', { 
        //     query: "myid=up{{ \Auth::user()->id }}&reqid={{ $request->id }}" 
        // });

        this.pubnub = new PubNub({ 
            publishKey : '{{ env('PUBNUB_PUB_KEY') }}',
            subscribeKey : '{{ env('PUBNUB_SUB_KEY') }}'
        });

        this.pubnub.subscribe({channels:[this.channel]});

        this.pubnub.addListener({
            message: function(data) {
                console.log("New Message :: "+JSON.stringify(data));
                if(data.message){
                    chatBox.appendChild(messageTemplate(data.message));
                    $(chatBox).animate({
                        scrollTop: chatBox.scrollHeight,
                    }, 500);
                }
            }
        });

        this.pubnub.history(
            { 
                channel: this.channel
            },
            function(status, response) {
                console.log('Pubnub History', response);
                $(response.messages).each(function(index, message) {
                    chatBox.appendChild(messageTemplate(message.entry));
                })
                $(chatBox).animate({
                        scrollTop: chatBox.scrollHeight,
                }, 500);
            }
        );

        // this.socket.on('connected', function (data) {
        //     socketState = true;
        //     chatInput.enable();
        //     console.log('Connected :: '+data);
        // });

        // this.socket.on('message', function (data) {
        //     console.log("New Message :: "+JSON.stringify(data));
        //     if(data.message){
        //         chatBox.appendChild(messageTemplate(data));
        //         $(chatBox).animate({
        //             scrollTop: chatBox.scrollHeight,
        //         }, 500);
        //     }
        // });

        // this.socket.on('disconnect', function (data) {
        //     socketState = false;
        //     chatInput.disable();
        //     console.log('Disconnected from server');
        // });
    }

    chatSockets.prototype.sendMessage = function(data) {
        // console.log('SendMessage'+data);

        data = {};
        data.type = 'up';
        data.message = text;
        data.user_id = "{{ \Auth::user()->id }}";
        data.request_id = "{{ $request->id }}";
        data.provider_id = "{{ $request->provider->id }}";

        // this.socket.emit('send message', data);

        this.pubnub.publish({
            channel : this.channel,
            message : data
        });
    }

    socketClient = new chatSockets();
    socketClient.initialize();

    chatInput.enable = function() {
        // console.log('Chat Input Enable');
        this.disabled = false;
    };

    chatInput.clear = function() {
        // console.log('Chat Input Cleared');
        this.value = "";
    };

    chatInput.disable = function() {
        // console.log('Chat Input Disable');
        this.disabled = true;
    };

    chatInput.addEventListener("keyup", function (e) {
        if (e.which == 13) {
            sendMessage(chatInput);
            return false;
        }
    });

    chatSend.addEventListener('click', function() {
        sendMessage(chatInput);
    });
    

    function sendMessage(input) {
        text = input.value.trim();
        if(text != '') {

            message = {};
            message.type = 'up';
            message.message = text;

            socketClient.sendMessage(text);
            chatInput.clear();
            // chatBox.appendChild(messageTemplate(message));
            // $(chatBox).animate({
            //     scrollTop: chatBox.scrollHeight,
            // }, 500);
        }
    }

    // $.get('{{ url("/chat/message") }}', {
    //     request_id: '{{ $request->id }}'
    // })
    // .done(function(response) {
    //     for (var i = (response.messages.length - 10 >= 0 ? response.messages.length - 10 : 0); i < response.messages.length; i++) {
    //         chatBox.appendChild(messageTemplate(response.messages[i]));
    //         $(chatBox).animate({
    //             scrollTop: chatBox.scrollHeight,
    //         }, 500);
    //     }
    // })
    // .fail(function(response) {
    //     // console.log(response);
    // })
    // .always(function(response) {
    //     // console.log(response);
    // });
</script>
@endif
@endsection

@section('styles')
<style>
    .chat-box {
        padding: 15px; 
        background-color: #fff;
    }

    .chat-box p {
        margin-bottom: 0;
    }

    .chat-left {
        max-width: 500px;
        margin-bottom: 10px;
    }

    .chat-left p {
        color: #fff;
        background: #37b38b;
        padding: 10px;
        font-size: 12px;
        border-radius: 0px 20px 20px 20px;
        display: inline-block;
    }

    .chat-right p {
        background: #ccc;
        padding: 10px;
        font-size: 12px;
        border-radius: 20px 0px 20px 20px;
        display: inline-block;
    }

    .chat-right {
        max-width: 500px;       
        float: right;
        margin-bottom: 10px;
    }

    .m-0 {
        margin: 0;
    }
</style>
@endsection
