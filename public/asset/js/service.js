function initAutocomplete(){

        var originLatitude = document.getElementById('origin_latitude');
        var originLongitude = document.getElementById('origin_longitude');

        var lat = current_latitude,
        lng = current_longitude,
        latlng = new google.maps.LatLng(lat, lng),
        image = 'http://www.google.com/intl/en_us/mapfiles/ms/micons/blue-dot.png'; 

        var mapOptions = {           
            center: new google.maps.LatLng(lat, lng),           
            zoom: 15,           
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            styles : [{"elementType":"geometry","stylers":[{"color":"#f5f5f5"}]},{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"elementType":"labels.text.fill","stylers":[{"color":"#616161"}]},{"elementType":"labels.text.stroke","stylers":[{"color":"#f5f5f5"}]},{"featureType":"administrative.land_parcel","elementType":"labels.text.fill","stylers":[{"color":"#bdbdbd"}]},{"featureType":"landscape.man_made","elementType":"geometry","stylers":[{"color":"#e4e8e9"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#eeeeee"}]},{"featureType":"poi","elementType":"labels.text.fill","stylers":[{"color":"#757575"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#e5e5e5"}]},{"featureType":"poi.park","elementType":"geometry.fill","stylers":[{"color":"#7de843"}]},{"featureType":"poi.park","elementType":"labels.text.fill","stylers":[{"color":"#9e9e9e"}]},{"featureType":"road","elementType":"geometry","stylers":[{"color":"#ffffff"}]},{"featureType":"road.arterial","elementType":"labels.text.fill","stylers":[{"color":"#757575"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#dadada"}]},{"featureType":"road.highway","elementType":"labels.text.fill","stylers":[{"color":"#616161"}]},{"featureType":"road.local","elementType":"labels.text.fill","stylers":[{"color":"#9e9e9e"}]},{"featureType":"transit.line","elementType":"geometry","stylers":[{"color":"#e5e5e5"}]},{"featureType":"transit.station","elementType":"geometry","stylers":[{"color":"#eeeeee"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#c9c9c9"}]},{"featureType":"water","elementType":"geometry.fill","stylers":[{"color":"#9bd0e8"}]},{"featureType":"water","elementType":"labels.text.fill","stylers":[{"color":"#9e9e9e"}]}]
     
        },
        map = new google.maps.Map(document.getElementById('map'), mapOptions),
        marker = new google.maps.Marker({
            position: latlng,
            map: map,
            icon: image
         });

        var input = document.getElementById('pac-input');         
        var autocomplete = new google.maps.places.Autocomplete(input); 

        autocomplete.bindTo('bounds', map); 
        var infowindow = new google.maps.InfoWindow(); 


        google.maps.event.addListener(autocomplete, 'place_changed', function() {
        infowindow.close();
            var place = autocomplete.getPlace();
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);  
            }
            
            moveMarker(place.name, place.geometry.location);
        });  


         $("input").focusin(function () {
            $(document).keypress(function (e) {
                if (e.which == 13) {
                    infowindow.close();
                    var firstResult = $(".pac-container .pac-item:first").text();
                    
                    var geocoder = new google.maps.Geocoder();
                    geocoder.geocode({"address":firstResult }, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            var lat = results[0].geometry.location.lat(),
                                lng = results[0].geometry.location.lng(),
                                placeName = results[0].address_components[0].long_name,
                                latlng = new google.maps.LatLng(lat, lng);
                            moveMarker(placeName, latlng);
                            // $("#s_address").val(firstResult);
                        }
                    });
                }
            });
        });
         
         function moveMarker(placeName, latlng){
            originLatitude.value = latlng.lat();
            originLongitude.value = latlng.lng();
            marker.setIcon(image);
            marker.setPosition(latlng);
            infowindow.setContent(placeName);
            infowindow.open(map, marker);
         }

     }