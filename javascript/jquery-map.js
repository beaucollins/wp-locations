(function($){
  
  $.fn.toMarker = function(onMarkerClick, options){
    var settings = $.extend({
      image:false
    }, options);
    return this.map(function(){
      var $t = $(this);
			var marker;
      if($t.data('marker')){
        marker = $t.data('marker');
      }else{
	      var address = $(this).toAddress()[0];
		    marker = new google.maps.Marker({
	        clickable: true,
	        title: address.name,
	        position: new google.maps.LatLng(address.geo.lat, address.geo.lng),
	      });
	      
	      if (settings.image) {
	        marker.setIcon(settings.image);
	      };
	      
	      $t.data('marker', marker);
	  	}
      
      if(onMarkerClick){
        google.maps.event.addListener(marker, 'click', onMarkerClick);
      }
      return marker;
    })
  };
  
	$.fn.eachWithMarker = function(fn){
		return this.each(function(i, element){
			fn.call(this, $(this).toMarker()[0], i, element);
		});
	}

  $.fn.toAddress = function(){
    return this.map(function(){
      var $t = $(this);
      return {
        name: $t.find('.fn').text(),
        street: $t.find('.street-address').text(),
        city: $t.find('.locality').text(),
        state: $t.find('.region').text(),
        zip: $t.find('.postal-code').text(),
        geo: {
          lat: $t.find('.latitude').text(),
          lng: $t.find('.longitude').text()
        },
        telephone: $t.find('.tel').text()
      }
    });
  }
  
  $.fn.toResultList = function(){
    return this.map(function(){
      return $('<li>Hello</li>');
    });
  }
  
  $.fn.toResultListItem = function(){
    var adr = this.toAddress()[0];
    return $('<li><a href="#">' + 
    '<span class="name">' + adr['name'] + '</span>' +
    '<span class="details"><span class="street-address">' + adr['street'] + '</span>' +
    '<span class="city-state-zip">' + adr['city'] + ', ' + adr['state'] + " " + adr['zip'] + '</span>' +
    '<span class="telephone">' + adr['telephone'] + '</span></span>' +
    '<span class="position"></span>' +
    '</a></li>');
  }
  
  $.fn.plot = function(map, onMarkerClick){
    return this.toMarker().each(function(){
      if(this.getMap() != map){
				this.setMap(map);
				if(onMarkerClick) google.maps.event.addListener(this, 'click', onMarkerClick);
			}
    });    
  };

	$.fn.unplot = function(){
		return this.toMarker().each(function(){
			this.setMap(null);
			google.maps.event.clearInstanceListeners(this);
		});
	}
  
  $.fn.withinBounds = function(bounds){
    return this.filter(function(){
      return bounds.contains($(this).toMarker()[0].getPosition());
    });
  }

  $.fn.outsideBounds = function(bounds){
    return this.filter(function(){
      return !bounds.contains($(this).toMarker()[0].getPosition());
    });
  }
  
  // right now kilometers
  $.fn.withinDistance = function(origin, distance){
    return this.filter(function(){
      return $(this).distanceFrom(origin) <= distance;
    });
  }
  
  $.fn.distanceFrom = function(origin){
    return google.maps.geometry.spherical.computeDistanceBetween(origin, this.toMarker()[0].getPosition());
  }
  
  $.fn.byDistanceFrom = function(coords){
    return this.sort(function(a, b){
      var d1 = $(a).distanceFrom(coords);
      var d2 = $(b).distanceFrom(coords);
      return d1 > d2 ? 1 : d2 > d1 ? -1 : 0;
    });
  }

	$.fn.eachByDistance = function(point, fn){
		return this.byDistanceFrom().each(function(index){
			fn.call(this, $(this).distanceFrom(point), index, this);
		});
	}
	
	$.fn.eachByMiles = function(point, fn){
		return this.byDistanceFrom(point).each(function(index){
			fn.call(this, $(this).distanceFrom(point)/1609.344, index, this);
		});
	}
  
  $.fn.sort = function(fun){
    return $(this.get().sort(fun));
  }
  
  $.fn.toBounds = function(origin){
    var bounds = new google.maps.LatLngBounds();
    bounds.extend(origin);
    this.each(function(){
      bounds.extend($(this).toMarker()[0].getPosition());
    });
    return bounds;
  }
    
})(jQuery);

