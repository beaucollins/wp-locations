<?php

add_action('wp_head', 'ri_map_style');

function ri_map_style(){
  ?>
<style type="text/css" media="screen">
  
  #ri-locations:after                 { content: '.'; clear:both; display:block; height:0; visibility:hidden; }
  #ri-map                             { height:480px; float: right; width: 640px; }
  #ri-map-search                      { float:left; width: 300px; background:#EEE; }
  #ri-map-search-form                 { position:relative; margin-right:70px; }
  #ri-search                          { display:block; width:100%; }
  #ri-search-submit                   { position:absolute; right:-65px; top:0; }
  #ri-map-search-results .current     { background:#FD999B; }
  #ri-map-search-results .ri-map-result { padding:5px; position:relative; }
  .ri-map-result a.url                  { display:block; font-size:14px;}
  .ri-map-result .address             { display:block; }
  .address .geo    { display:none; }
  .ri-map-result .street-address     { display:block; }
  .ri-map-result .country           { display:none; }
  .ri-result-distance { position:absolute; top:2px; right:2px; background:#CCC;}
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
    var locations = $('.vcard').hide();
    
    google.maps.event.addListener(info_window, 'closeclick', function(){
      $('#ri-result-list .current').removeClass('current');
    });
    locations.eachWithMarker(function(marker, i, element){
      marker.setMap(map);
      google.maps.event.addListener(marker, 'click', function(){
        info_window.setContent($(element).clone().show()[0]);
        info_window.open(map, marker);
        $('#ri-map').trigger('map.marker-clicked', [marker, element]);
      });
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
          map.setZoom(14);
          locations
            .eachByMiles(result.geometry.location, function(distance, index, element){
              //create the item to show as a result
              
              var result_list = $('#ri-map-search-results');
              var $e = $(element);
              var marker = $(element).toMarker()[0];
              var result_item = $("<div class='ri-map-result'>" + $e.html() + '</div>')
                                  .append("<div class='ri-result-distance'>" + (Math.round(distance*10)/10) + " miles</div>")
                                  .appendTo(result_list)
                                  .click(function(e){
                                    if(marker.getMap() == null) marker.setMap(map);
                                    e.preventDefault();
                                    google.maps.event.trigger(marker, 'click');
                                  });
              $('#ri-map').bind('map.marker-clicked', function(event, clicked_marker){
                //
                console.log("cliced", clicked_marker, marker);
                if(clicked_marker == marker){
                  result_item.addClass('current');
                }else{
                  result_item.removeClass('current');
                }
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