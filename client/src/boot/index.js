/* SilverWare Google Maps Boot
===================================================================================================================== */

import $ from 'jquery';

$(function() {
  
  // Load Google Maps Platform:
  
  if ($('body').is('[data-google-api-key]')) {
    
    // Build Parameter Array:
    
    var params = {
      key: $('body').data('google-api-key')
    };
    
    // Load Google Maps Script:
    
    $.getScript('//maps.googleapis.com/maps/api/js?' + $.param(params), function() {
      
      // Initialise Components:
      
      $('.googlemapcomponent').each(function() {
        
        var $self = $(this);
        var $map  = $self.find('.google-map');
        
        var coords = {
          lat: parseFloat($map.data('latitude')),
          lng: parseFloat($map.data('longitude'))
        };
        
        // Create Map:
        
        var map = new google.maps.Map($map[0], {
          zoom: parseInt($map.data('zoom')),
          mapTypeId: $map.data('map-type'),
          center: coords
        });
        
        // Create Markers:
        
        $.each($map.data('markers'),  function(key, value) {
          
          var marker = new google.maps.Marker({
            title: value.title,
            label: value.label,
            position: {
              lat: parseFloat(value.latitude),
              lng: parseFloat(value.longitude)
            },
            map: map
          });
          
          // Create Info Window:
          
          if (value.showInfoWindow) {
            
            var infoWindow = new google.maps.InfoWindow({
              content: value.content
            });
            
            marker.addListener('click', function() {
              infoWindow.open(map, marker);
            });
            
          }
          
        });
        
      });
      
    });
    
  }
  
});
