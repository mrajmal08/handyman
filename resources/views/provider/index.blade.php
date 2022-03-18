@extends('provider.layout.app')

@section('content')
@if(Auth::user()->status=='document')
<div class="pro-dashboard-head">
    <div class="container">
        <a href="#" class="pro-head-link active">@lang('provider.document_pending')</a>
    </div>
</div>
<div class="pro-dashboard-content">
    <div class="container">
        <div class="dash-content">
            <div class="row no-margin">
                <div className="col-md-12">  
                    <form method="POST" action="provider/profile/available">
                        <div class="offline">
                                <img src="/asset/img/offline.gif"/>
                            </div>                  
                        <a href="{{ route('provider.documents.index') }}" class="full-primary-btn">@lang('provider.document_upload')</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="pro-dashboard-head">
    <div class="container">
        <a href="#" class="pro-head-link active">@lang('main.service') Now</a>
    </div>
</div>
<div class="pro-dashboard-content">
    <div class="container">
        <div class="dash-content" id="trip-container">
            <div class="row no-margin" >

            </div>
        </div>
    </div>
</div>
@endif
@endsection
@section('styles')
<style type="text/css">
    .chatwindowscroll{
    overflow-y:scroll;
    height:400px;
    }

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

@section('scripts')
<script type="text/javascript" src="{{asset('asset/js/rating.js')}}"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ Setting::get('map_key', '') }}&libraries=places" defer></script>
<script type="text/javascript">
    var map;
    var routeMarkers = {
                source: {
                    lat: 0,
                    lng: 0,
                },
                destination: {
                    lat: 0,
                    lng: 0,
                }
            };
    var zoomLevel = 13;
    var directionsService;
    var directionsDisplay;

    function initMap() {
        // Basic options for a simple Google Map
        var center = new google.maps.LatLng('13', '80');
        
        directionsService = new google.maps.DirectionsService;
        directionsDisplay = new google.maps.DirectionsRenderer;
        // For more options see: https://developers.google.com/maps/documentation/javascript/reference#MapOptions

        var mapOptions = {
            // How zoomed in you want the map to start at (always required)
            zoom: zoomLevel,
            disableDefaultUI: true,
            // The latitude and longitude to center the map (always required)
            center: center,

            // Map styling
            styles: [
                {
                    elementType:"geometry",
                    stylers:[
                        {
                            color:"#f5f5f5"
                        }
                    ]
                },
                {
                    elementType:"labels.icon",
                    stylers:[
                        {
                            visibility:"off"
                        }
                    ]
                },
                {
                    elementType:"labels.text.fill",
                    stylers:[
                        {
                            color:"#616161"
                        }
                    ]
                },
                {
                    elementType:"labels.text.stroke",
                    stylers:[
                        {
                            color:"#f5f5f5"
                        }
                    ]
                },
                {
                    featureType:"administrative.land_parcel",
                    elementType:"labels.text.fill",
                    stylers:[
                        {
                            color:"#bdbdbd"
                        }
                    ]
                },
                {
                    featureType:"poi",
                    elementType:"geometry",
                    stylers:[
                        {
                            color:"#eeeeee"
                        }
                    ]
                },
                {
                    featureType:"poi",
                    elementType:"labels.text.fill",
                    stylers:[
                        {
                            color:"#757575"
                        }
                    ]
                },
                {
                    featureType:"poi.park",
                    elementType:"geometry",
                    stylers:[
                        {
                            color:"#e5e5e5"
                        }
                    ]
                },
                {
                    featureType:"poi.park",
                    elementType:"geometry.fill",
                    stylers:[
                        {
                            color:"#7de843"
                        }
                    ]
                },
                {
                    featureType:"poi.park",
                    elementType:"labels.text.fill",
                    stylers:[
                        {
                            color:"#9e9e9e"
                        }
                    ]
                },
                {
                    featureType:"road",
                    elementType:"geometry",
                    stylers:[
                        {
                            color:"#ffffff"
                        }
                    ]
                },
                {
                    featureType:"road.arterial",
                    elementType:"labels.text.fill",
                    stylers:[
                        {
                            color:"#757575"
                        }
                    ]
                },
                {
                    featureType:"road.highway",
                    elementType:"geometry",
                    stylers:[
                        {
                            color:"#dadada"
                        }
                    ]
                },
                {
                    featureType:"road.highway",
                    elementType:"labels.text.fill",
                    stylers:[
                        {
                            color:"#616161"
                        }
                    ]
                },
                {
                    featureType:"road.local",
                    elementType:"labels.text.fill",
                    stylers:[
                        {
                            color:"#9e9e9e"
                        }
                    ]
                },
                {
                    featureType:"transit.line",
                    elementType:"geometry",
                    stylers:[
                        {
                            color:"#e5e5e5"
                        }
                    ]
                },
                {
                    featureType:"transit.station",
                    elementType:"geometry",
                    stylers:[
                        {
                            color:"#eeeeee"
                        }
                    ]
                },
                {
                    featureType:"water",
                    elementType:"geometry",
                    stylers:[
                        {
                            color:"#c9c9c9"
                        }
                    ]
                },
                {
                    featureType:"water",
                    elementType:"geometry.fill",
                    stylers:[
                        {
                            color:"#9bd0e8"
                        }
                    ]
                },
                {
                    featureType:"water",
                    elementType:"labels.text.fill",
                    stylers:[
                        {
                            color:"#9e9e9e"
                        }
                    ]
                }
            ]
        };

        // Get the HTML DOM element that will contain your map 
        // We are using a div with id="map" seen below in the <body>
        var mapElement = document.getElementById('map');

        // Create the Google Map using out element and options defined above
        map = new google.maps.Map(mapElement, mapOptions);

        navigator.geolocation.getCurrentPosition(function (position) { 
            center = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            map.setCenter(center);

            var marker = new google.maps.Marker({
                map: map,
                anchorPoint: new google.maps.Point(0, -29),
            });

            marker.setPosition(center);
            marker.setVisible(true);
        });

    }

    function updateMap(route) {

        // console.log('updateMap', route, routeMarkers);
        // var markerSecond = new google.maps.Marker({
        //     map: map,
        //     anchorPoint: new google.maps.Point(0, -29)
        // });

        // source = new google.maps.LatLng('13', '80');
        // destination = new google.maps.LatLng('13', '80');

        // marker.setVisible(false);
        // marker.setPosition(source);

        // markerSecond.setVisible(false);
        // markerSecond.setPosition(destination);

        // var bounds = new google.maps.LatLngBounds();
        // bounds.extend(marker.getPosition());
        // bounds.extend(markerSecond.getPosition());
        // map.fitBounds(bounds);

        if(routeMarkers.source.lat == route.source.lat &&
            routeMarkers.source.lng == route.source.lng &&
            routeMarkers.destination.lat == route.destination.lat &&
            routeMarkers.destination.lng == route.destination.lng) {

        } else {

            routeMarkers = route;
            
            directionsDisplay.set('directions', null);
            directionsDisplay.setMap(map);

            directionsService.route({
                origin: route.source,
                destination: route.destination,
                travelMode: google.maps.TravelMode.DRIVING
            }, function(result, status) {
                if (status == google.maps.DirectionsStatus.OK) {
                    directionsDisplay.setDirections(result);
                }
            });
        }

    }
</script>
{{-- <script src="https://cdn.socket.io/socket.io-1.4.5.js"></script> --}}
<script src=https://cdn.pubnub.com/sdk/javascript/pubnub.4.0.11.min.js></script>
<script type="text/javascript">
    var defaultImage = "{{ asset('user_default.png') }}";
    var chatBox, chatInput, chatSend;
    var chatRequestId = 0;
    var chatUserId = 0;
    var chatload = 0;
    var initialized = false;
    var socketClient;

    function updateChatParam(pmrequestid, pmuserid) {
        // console.log('Chat Params', pmrequestid, pmuserid);
        chatRequestId = pmrequestid;
        chatUserId = pmuserid;

        if(initialized == false) {
            socketClient.channel = pmrequestid;
            socketClient.initialize();
            socketClient.channel = pmrequestid;
            socketClient.pubnub.subscribe({channels:[socketClient.channel]});
            initialized = true;            
        }

        // if(chatload == 0){
        //     $.get('{{ route("provider.chat.message") }}', {
        //         request_id: chatRequestId
        //     })
        //     .done(function(response) {
        //         console.log(response);
        //         for (var i = (response.messages.length - 10 >= 0 ? response.messages.length - 10 : 0); i < response.messages.length; i++) {
        //             chatBox.appendChild(messageTemplate(response.messages[i]));
        //             $(chatBox).animate({
        //                 scrollTop: chatBox.scrollHeight,
        //             }, 500);
        //         }
        //     })
        //     .fail(function(response) {
        //         // console.log(response);
        //     })
        //     .always(function(response) {
        //         // console.log(response);
        //     });
        //     chatload = 1;
        // }
    }

    var messageTemplate = function(data) {
        var message = document.createElement('div');
        var messageContact = document.createElement('div');
        var messageText = document.createElement('p');

        //messageText.className = "chat-text";
        messageText.innerHTML = data.message;

        if(data.type == 'pu') {
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
    
    function initChat(){

        chatBox = document.getElementById('chat-box');
        chatInput = document.getElementById('chat-input');
        chatSend = document.getElementById('chat-send');

        chatSockets = function () {
            // this.channel = chatRequestId;
            // this.socket = undefined;
            //this.user_picture = defaultImage;
            //this.provider_picture = "{{ \Auth::guard('provider')->user()->avatar }}" == "" ? defaultImage : "{{ \Auth::guard('provider')->user()->avatar }}";
        }

        chatSockets.prototype.initialize = function() {
            //console.log(chatRequestId+"===="+chatUserId);
            // this.socket = io('{{ env("SOCKET_SERVER") }}', { query: ("myid=pu{{ \Auth::guard('provider')->user()->id }}&reqid="+chatRequestId) });
            // //this.socket = io('{{ env("SOCKET_SERVER") }}');
            
            this.pubnub = new PubNub({ 
                publishKey : '{{ env('PUBNUB_PUB_KEY') }}',
                subscribeKey : '{{ env('PUBNUB_SUB_KEY') }}'
            });

            console.log('Connect Channel', this.channel);

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
            console.log('SendMessage'+data);

            data = {};
            data.type = 'pu';
            data.message = text;
            data.user_id = chatUserId;
            data.request_id = chatRequestId;
            data.provider_id = "{{ \Auth::guard('provider')->user()->id }}";

            // this.socket.emit('send message', data);

            this.pubnub.publish({
                channel : this.channel,
                message : data
            });
        }

        socketClient = new chatSockets();

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
                message.type = 'pu';
                message.message = text;

                socketClient.sendMessage(text);
                // chatBox.appendChild(messageTemplate(message));
                // $(chatBox).animate({
                //     scrollTop: chatBox.scrollHeight,
                // }, 500);
                chatInput.clear();
            }
        }
        
        chatInput.enable();
    }
</script>
@endsection