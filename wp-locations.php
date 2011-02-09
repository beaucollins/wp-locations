<?php
/*
Plugin Name: Locations
Plugin URI: http://github.com/beaucollins/wp-locations
Description: Locations with a map
Author: Beau Collins
Author URI: http://github.com/beaucollins/
Version: 0.1
Stable tag: 0.1
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


add_action( 'init', 'ri_init' );

function ri_init() {
  global $wp_rewrite;
  wp_register_script('ri-google-maps', 'http://maps.google.com/maps/api/js?sensor=false');
  wp_register_style('ri-map-admin', plugin_dir_url(__FILE__) . 'css/admin.css');
  register_post_type( 'ri_location', array(
    'labels' => array(
      'name' => __( 'Locations' ),
      'singular_name' => __( 'Location' )
    ),
    'menu_position' => 6,
    'description' => __( 'Physical locations that can be displayed on a map' ),
    'menu_icon' => plugin_dir_url(__FILE__) . 'images/icon.png',
    'public' => true,
    //'has_archive' => true, works in 3.1
    'rewrite' => array('slug' => 'locations'),
    'show_in_nav_menus' => true
  ));

}

add_action( 'generate_rewrite_rules', 'ri_rewrite_rules' );

function ri_rewrite_rules( $wp_rewrite ){
  $wp_rewrite->add_rule('locations/?$', 'index.php?post_type=ri_location', 'top');
}

add_action( 'template_redirect', 'ri_index_template' );

function ri_index_template(){
  global $wp_query;
  echo "<pre>";
  print_r($wp_query);
  print_r($_GET);
  echo "</pre>";
  die('Heelo');
  
}

add_action( 'admin_init', 'ri_admin_init' );

function ri_admin_init() {
  wp_enqueue_script('ri-google-maps');
  wp_enqueue_style('ri-map-admin');
  add_meta_box( 'ri-location', 'Location', 'ri_admin_map', 'ri_location'  );  
}

add_action( 'save_post', 'ri_location_save', 10, 2 );

function ri_location_save($post_id, $post){
  if( !wp_verify_nonce( $_POST['rilocation_nonce'], plugin_basename(__FILE__) ) ) return $post_id;
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
  
  // save the lat/lng
  // save the formatted address
  
  update_post_meta($post_id, 'ri_lat', $_POST['ri_lat']);
  update_post_meta($post_id, 'ri_lng', $_POST['ri_lng']);
  update_post_meta($post_id, 'ri_formatted_address', $_POST['ri_formatted_address']);
    
}

function ri_admin_map($post = nil) {
  wp_nonce_field( plugin_basename(__FILE__), 'rilocation_nonce' );
  $meta = get_post_custom($post->ID);
  $location = array('lat' => $meta['ri_lat'][0], 'lng' => $meta['ri_lng'][0]);
  
?>
<div id="ri-locate-textbox"><input type="text" id="ri-locate-field" /><input type="submit" value="Locate" class="button-primary" id="ri-locate-submit"></div>
<div id="ri-map-control"></div>
<div id="ri-address">
  <input type="hidden" id="ri-lat" name="ri_lat" value="<?php echo $meta['ri_lat'][0] ;?>" />
  <input type="hidden" id="ri-lng" name="ri_lng" value="<?php echo $meta['ri_lng'][0] ;?>" />
  <label for="ri-formatted-address">Display Address</label>
  <textarea name="ri_formatted_address" id="ri-formatted-address"><?php print_r($meta['ri_formatted_address'][0]); ?></textarea>
</div>
<script type="text/javascript" charset="utf-8">
  (function($){
    var map = new google.maps.Map(document.getElementById("ri-map-control"), {
      zoom:6,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    });

    var geocoder = new google.maps.Geocoder();
    var marker = new google.maps.Marker();

    <?php if ($location['lat'] && $location['lng']): ;?>
    var initialLocation = new google.maps.LatLng(<?php echo $location['lat'] ;?>, <?php echo $location['lng'] ;?>);
    map.setCenter(initialLocation);
    map.setZoom(12);
    marker.setPosition(initialLocation);
    marker.setMap(map);
    <?php else: ?>
    var initialLocation = new google.maps.LatLng(40.69847032728747, -73.9514422416687);
    map.setCenter(initialLocation);
    <?php endif; ?>
    
    
    
    var locate = function(){
      geocoder.geocode({'address':$('#ri-locate-field').val(), 'bounds': map.getBounds() }, function(results, status){
        if (status == google.maps.GeocoderStatus.OK) {
          map.setCenter(results[0].geometry.location);
          map.setZoom(12);
          marker.setPosition(results[0].geometry.location);
          marker.setMap(map);
          $('#ri-formatted-address').val(results[0].formatted_address);
          $('#ri-lat').val(results[0].geometry.location.lat());
          $('#ri-lng').val(results[0].geometry.location.lng());
        }else{
          alert("Could not locate: " + status);
        }
      });
    }
    
    $('#ri-locate-submit').click(function(e){
      e.preventDefault();
      locate();
    });
    $('#ri-locate-textbox').keypress(function(e){
      if(e.which == 13){
        e.preventDefault();
        locate();        
      }
    })

  })(jQuery);
</script>
<?php  
}

