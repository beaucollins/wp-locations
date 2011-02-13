(function($){
  
  $.fn.toMarker = function(onMarkerClick){

    return this.map(function(){
      var $t = $(this);
      if($t.data('marker')){
        return $t.data('marker');
      }
      
      var address = $(this).toAddress()[0];
      var marker = new google.maps.Marker({
        clickable: true,
        title: address.name,
        position: new google.maps.LatLng(address.geo.lat, address.geo.lng),
      });
      if(onMarkerClick){
        
        google.maps.event.addListener(marker, 'click', onMarkerClick);
      }
      $t.data('marker', marker);
      return marker;
    })
  };
  
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
    this.toMarker(onMarkerClick).each(function(){
      this.setMap(map);
    });
    return this;
    
  };
  
  $.fn.withinBounds = function(bounds){
    return this.filter(function(){
      return bounds.contains($(this).toMarker()[0].getPosition());
    });
  }
  
  // right now kilometers
  $.fn.withinDistance = function(origin, distance){
    return this.filter(function(){
      return $(this).distanceFrom(origin) <= distance;
    });
  }
  
  $.fn.distanceFrom = function(origin){
    var p1 = origin;
    var p2 = this.toMarker()[0].getPosition();
		google.maps.geometry.spherical.computeDistanceBetween(p1, p2);
    return d;
  }
  
  $.fn.byDistanceFrom = function(coords){
    return this.sort(function(a, b){
      var d1 = $(a).distanceFrom(coords);
      var d2 = $(b).distanceFrom(coords);
      return d1 > d2 ? 1 : d2 > d1 ? -1 : 0;
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


// // Infowin class for displaying a miniature info window. Does not
// // respond to any events - so you should show and remove the
// // overlay yourself as necessary.
// function Infowin(latlng, html) {
//         this.latlng_ = latlng;
//         this.html_ = html;
//         this.prototype = new google.maps.OverlayView();
// 
//         // Creates the DIV representing the infowindow
//         this.initialize = function(map) {
//                 var div = $('<div />');
//                 div.css({
//                         position : 'absolute',
//                         width : 234
//                 }).appendTo(map.getPane(G_MAP_FLOAT_PANE))
// 
//                 this.map_ = map;
//                 this.div_ = div;
// 
//                 this.update(html);
//         }
// 
//         this.update = function(html){
//                 this.html_ = html;
// 
//                 this.div_.empty();
// 
//                 $('<div />').css({
//                         'background-image' : 'url(/images/infow-top.png)',
//                         height : 14,
//                         padding: '0 0 0 0'
//                 }).appendTo(this.div_);
// 
//                 var content = $('<div />').addClass('infowin-content').css({
//                         'position' : 'relative',
//                         'overflow' : 'hidden',
//                         'max-height' : 100,
//                         'top' : -5
//                 }).html(html);
// 
//                 $('<div />').css({
//                         'background-image' : 'url(/images/infow-bottom.png)',
//                         'background-position' : 'bottom left',
//                         'padding' : '0 10px 30px 10px'
//                 }).append(content).appendTo(this.div_);
// 
//                 this.redraw(true);
//         }
// 
//         // Remove the main DIV from the map pane
//         this.remove = function() {
//           this.div_.remove();
//         }
// 
//         // Copy our data to a new instance
//         this.copy = function() {
//           return new Infowin(this.latlng_, this.html_);
//         }
// 
//         // Redraw based on the current projection and zoom level
//         this.redraw = function(force) {
//                 if (!force) return;
// 
//                 var point = this.map_.fromLatLngToDivPixel(this.latlng_);
// 
//                 // Now position our DIV based on the DIV coordinates of our bounds
//                 this.div_.css({
//                         left : point.x - 108,
//                         top : point.y - this.div_.height() - 22
//                 });
//         }
// }
//  
// 
// 
