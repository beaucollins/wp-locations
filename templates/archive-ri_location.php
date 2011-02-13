<?php

add_action('wp_head', 'ri_map_style');

function ri_map_style(){
  ?>
  <style type="text/css" media="screen">
  
    #ri-locations:after {
      content: '.';
      clear:both;
      display:block;
      height:0;
      visibility:hidden;
    }
    #ri-map {
      height:480px;
      float: right;
      width: 640px;
    }
    
    #ri-map-search {
      float:left;
      width: 300px;
      background:#EEE;
    }
    
    #ri-map-search-form {
      position:relative;
      margin-right:70px;
    }
    
    #ri-search {
      display:block;
      width:100%;
    }
    
    #ri-search-submit {
      position:absolute;
      right:-65px;
      top:0;
    }
    
  </style>
  <?php
}

?>
<?php get_header(); ?>
<script type="text/javascript" charset="utf-8">
(function($){
  $(document).ready(function(){

    var map = new google.maps.Map(document.getElementById('ri-map'), {
      'zoom' : 3,
      'mapTypeId' : google.maps.MapTypeId.ROADMAP
    });
    
    var geocoder = new google.maps.Geocoder();
    var info_window = new google.maps.InfoWindow(); 
    google.maps.event.addListener(map, 'idle', function(e){
      //load all the markers in this area
      var bounds = map.getBounds();
      var locations  = $('.vcard').withinBounds(bounds);
      if(locations.length < 100){
        //remove markers from map and clear all listeners
        $('.vcard').outsideBounds(bounds).unplot();
        locations
          .plot(map, function(){
            console.log(this);
            info_window.setContent('Hello');
            info_window.open(map, this);
          });
      }else{
        //too many locations
      }
    });
    
    map.setCenter(new google.maps.LatLng(-47,0));
    
    $('#ri-map-search-form form').submit(function(e){
      e.preventDefault();
      var address = $('#ri-search').val();
      geocoder.geocode( { 'address' : address }, function(results, status){
        $('#ri-map-search-results').html('');
        if (status == google.maps.GeocoderStatus.OK) {
          var result = results[0];
          map.setCenter(result.geometry.location);
          map.setZoom(8);
          $('.vcard')
            .withinBounds(map.getBounds())
            .eachByMiles(result.geometry.location, function(distance, index, element){
              console.log(distance, index, element);
              //create the item to show as a result
              var $e = $(element);
              var marker = $(element).toMarker()[0];
              var result_item = $("<div class='ri-map-result'>" + $e.html() + '</div>')
                                  .append("<div class='ri-result-distance'>" + (Math.round(distance*10)/10) + "</div>")
                                  .appendTo($('#ri-map-search-results'))
                                  .click(function(e){
                                    e.preventDefault();
                                    console.log('Click');
                                    google.maps.event.trigger(marker, 'click');
                                  });
              
              
            })
        }else{
          console.log("Result:", status);
        };
      });
    });

  });
})(jQuery);
</script>

<div id="ri-locations">
  <div id="ri-map-search">
    <div id="ri-map-search-form">
      <form method="get">
        <input type="text" name="ri-search" id="ri-search" />
        <input type="submit" name="ri-search-submit" id="ri-search-submit" />
      </form>
    </div>
    <div id="ri-map-search-results">
    
    </div>
  </div>
  <div id="ri-map">
  
  </div>
</div>

<?php if( !have_posts() ):?>
  
<?php else: while( have_posts() ): the_post(); ?>

  <div id="locations-index">
    <div class="vcard">
      <a class="url fn org" href="<?php the_permalink();?>"><?php the_title(); ?></a>
      <span class="address">
        <?php ri_formatted_address(); ?>
        <?php ri_geo(); ?>
      </span>
    </div>
  </div>
<?php endwhile; endif; ?>
<?php get_footer(); ?>