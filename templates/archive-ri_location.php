<?php

add_action('wp_head', 'ri_map_style');

function ri_map_style(){
  ?>
<style type="text/css" media="screen">
  
#ri-locations:after                       { content: '.'; clear:both; display:block; height:0; visibility:hidden; }
#ri-map                                   { height:480px; float: right; width: 640px; }
#ri-map-search                            { float:left; width: 300px; background:#EEE; }
#ri-map-search-form                       { position:relative; margin-right:70px; }
#ri-search                                { display:block; width:100%; }
#ri-search-submit                         { position:absolute; right:-65px; top:0; }
#ri-map-search-results .current           { background:#FD999B; }
#ri-map-search-results .ri-map-result     { padding:5px; position:relative; }
#ri-map .vcard                            { width: 250px; margin:0 !important; }
.ri-map-result a.url, #ri-map a.url       { display:block; font-size:14px; }
.ri-map-result .address,
  #ri-map .address                        { display:block; }
.geo                             { display:none; }
.ri-map-result .street-address,
  #ri-map .street-address                 { display:block; }
.ri-map-result .country,
  #ri-map .country                        { display:none; }
.ri-result-distance                       { position:absolute; top:2px; right:2px; background:#CCC; }
.ri-map-result .about { display:none; }
.about p  { margin:0; padding:0;}
#ri-map .about  { margin:5px 0 0; padding:5px 0; border-top:1px solid #EEE; color:#666; }
</style>
  <?php
}

?>
<?php get_header(); ?>
<script type="text/javascript" charset="utf-8">
(function($){
  $(document).ready(function(){
    
    var marker_image = false;
    
    <?php if(ri_use_custom_placemarker()): ?>
    marker_image = "<?php echo ri_placemarker_image();?>";
    <?php endif; ?>
    
    <?php $default_map_view = ri_default_map_settings(); ?>
    var map = new google.maps.Map(document.getElementById('ri-map'), {
      'zoom' : <?php echo $default_map_view['zoom'] ;?>,
      'center' : new google.maps.LatLng(<?php echo $default_map_view['lat'] ?>, <?php echo $default_map_view['lng'];?> ),
      'mapTypeId' : google.maps.MapTypeId.ROADMAP
    });
    
    var geocoder = new google.maps.Geocoder();
    var info_window = new google.maps.InfoWindow({maxWidth:250}); 
    var locations = $('.vcard').hide();
    
    var result_list = $('#ri-map-search-results');
    locations.each(function(){
      var $e = $(this);
      var marker = $e.toMarker(false, {image:marker_image})[0];
      var result_item = $("<div class='ri-map-result'>" + $e.html() + '</div>')
                          .appendTo(result_list)
                          .click(function(e){
                            if(marker.getMap() == null) marker.setMap(map);
                            e.preventDefault();
                            google.maps.event.trigger(marker, 'click');
                          });
      $('#ri-map').bind('map.marker-clicked', function(event, clicked_marker){
        //
        if(clicked_marker == marker){
          result_item.addClass('current');
        }else{
          result_item.removeClass('current');
        }
      });
    })
    
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
              var marker = $(element).toMarker(false, {image:marker_image})[0];
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
                if(clicked_marker == marker){
                  result_item.addClass('current');
                }else{
                  result_item.removeClass('current');
                }
              });
              
            })
        }else{
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
  
<?php else: ?>
  
  <div id="locations-index">
  <?php while( have_posts() ): the_post(); ?>

    <div class="vcard">
      <a class="url fn org" href="<?php the_permalink();?>"><?php the_title(); ?></a>
      <?php ri_formatted_address(); ?>
      <?php ri_geo(); ?>
      <div class="about">
        <?php the_excerpt(); ?>
      </div>
    </div>
  <?php endwhile; ?>
  </div>
<?php endif; ?>
<?php get_footer(); ?>