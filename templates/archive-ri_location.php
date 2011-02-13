<?php

add_action('wp_head', 'ri_map_style');

function ri_map_style(){
  ?>
  <style type="text/css" media="screen">
    #ri-map {
      height:480px;
    }
  </style>
  <?php
}

?>
<?php get_header(); ?>
<script type="text/javascript" charset="utf-8">
(function($){
  $(document).ready(function(){

    var m = new google.maps.Map(document.getElementById('ri-map'), {
      'zoom' : 3,
      'mapTypeId' : google.maps.MapTypeId.ROADMAP
    });
    
    google.maps.event.addListener(m, 'idle', function(e){
      //load all the markers in this area
      var locations  = $('.vcard').withinBounds(m.getBounds());
      if(locations.length < 100){
        locations.toMarker().each(function(){
          console.log(this);
          if(this.getMap() != m) this.setMap(m);
        });
      }else{
        //too many locations
      }
    });
    
    m.setCenter(new google.maps.LatLng(-47,0));

  })
})(jQuery);
</script>
<div id="ri-map">
  
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