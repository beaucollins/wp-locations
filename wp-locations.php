<?php
/*
Plugin Name: Locations
Plugin URI: http://github.com/beaucollins/wp-locations
Description: Locations with a map
Author: Beau Collins
Author URI: http://github.com/beaucollins/
Version: 0.2
License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

require_once(plugin_dir_path(__FILE__) . 'lib/location.class.php');

add_action( 'init', 'ri_init' );

function ri_init() {
  global $wp_rewrite;
  wp_register_script('ri-google-maps', 'http://maps.google.com/maps/api/js?libraries=geometry&sensor=false');
  wp_register_script('ri-jquery-map', plugin_dir_url(__FILE__) . 'javascript/jquery-map.js');
  wp_enqueue_script('jquery');
  wp_enqueue_script('ri-google-maps');
  wp_enqueue_script('ri-jquery-map');
  wp_register_style('ri-map-admin', plugin_dir_url(__FILE__) . 'css/admin.css');
  register_post_type( 'ri_location', array(
    'labels' => array(
      'name' => __( 'Locations' ),
      'singular_name' => __( 'Location' )
    ),
    'supports' => array('title', 'excerpt'),
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
  $wp_rewrite->rules = array_merge(array('locations/?$' => 'index.php?post_type=ri_location'), $wp_rewrite->rules);
}

add_action( 'template_redirect', 'ri_index_template' );

function ri_index_template(){
  global $wp_query;
  if ( is_ri_location_archive() ) {
    // use the arhive-ri_location.php template
    // first look for it in the theme
    // then use the built in one
    if ($template = locate_template(array('archive-ri_location.php'))) {
      include($template);
      exit;
    }else{
      include(plugin_dir_path(__FILE__) . 'templates/archive-ri_location.php');
      exit;
    }
  }  
}

add_action( 'activate_plugin', 'ri_refresh_rewrite_rules');

function ri_refresh_rewrite_rules($plugin){
  flush_rewrite_rules();
}

function is_ri_location_archive() {
  return !is_single() && get_query_var('post_type') == 'ri_location';
}

function ri_formatted_address($p = nil) {
  global $post;
  if( $p === nil ) $p = $post;
  $address = ri_location_for_post($p->ID);
  ?>
  <span class="address">
    <span class="street-address"><?php echo $address->street_address ?></span>
    <span class="locality"><?php echo $address->locality ?></span>,
    <abbr title="<?php echo $address->region_name; ?>"><?php echo $address->region_abbreviation ?></abbr>
    <span class="postal-code"><?php echo $address->postal_code ?></span>
    <span class="country"><?php echo $address->country_name ?></span>
  </span>
  <?php
}

function ri_geo($p = nil){
  global $post;
  if( $p === nil ) $p = $post;
  
  $address = ri_location_for_post($p->ID);
  if( $address->hasLocation() ){
    ?>
    <span class="geo">
      <span class="latitude"><?php echo $address->lat ?></span>
      <span class="longitude"><?php echo $address->lng?></span>
    </span>
    <?php
  }
  
}

function ri_default_map_settings(){
  return get_option('ri-location-position', array('lat' => '38.13', 'lng' => '-94.13', 'zoom' => '3' ));
}

function ri_placemarker_image(){
  return get_option('ri-placemarker-image', false);
}

function ri_use_custom_placemarker(){
  $image = ri_placemarker_image();
  return !empty($image);
}

function ri_location_for_post($post_id = nil){
  global $post;
  if($post_id === nil) $post_id = $post->ID;
  
  return new RI_Location($post_id);
  
}

add_action( 'admin_init', 'ri_admin_init' );

function ri_admin_init() {
  wp_enqueue_script('ri-google-maps');
  wp_enqueue_style('ri-map-admin');
  add_meta_box( 'ri-location', 'Location', 'ri_admin_map', 'ri_location'  );  
}

add_action( 'admin_menu', 'ri_admin_menu' );

function ri_admin_menu(){
  add_submenu_page('edit.php?post_type=ri_location', 'Location Settings', 'Settings', 'manage_options', 'rilocation_settings', 'ri_location_settings');  
}

function ri_location_settings(){
  if(!current_user_can('manage_options')){
    wp_die( __('You do not have sufficient permissions to access this page.') );
  }
  
  if ( $_SERVER['REQUEST_METHOD'] == 'POST' && wp_verify_nonce( $_POST['rilocation_nonce'], plugin_basename(__FILE__) )  ) {
    $position = $_POST['riposition'];
    $placemarker = $_POST['riplacemarker'];
    update_option('ri-location-position', $position);
    update_option('ri-placemarker-image', $placemarker);
    ?>
    <div class="updated"><p><strong><?php _e('Location settings saved.', 'rilocation' ); ?></strong></p></div>    
    
    <?php
  }else{
    $position = ri_default_map_settings();
    $placemarker = ri_placemarker_image();
  }
  
  ?>
  <div class="wrap">
    <h2><?php echo __( 'Location Settings', 'rilocation') ?></h2>
    <form name="ri_location_settings" method="post" action="">
      <div>
        <?php wp_nonce_field( plugin_basename(__FILE__), 'rilocation_nonce' );?>
        <input type="hidden" value="<?php echo $position['lat'];?>" name="riposition[lat]" />
        <input type="hidden" value="<?php echo $position['lng'];?>" name="riposition[lng]" />
        <input type="hidden" value="<?php echo $position['zoom'];?>" name="riposition[zoom]" />
      </div>
      <table class="form-table">
        <tbody>
          <tr valign="top">
            <th scope="row">Default Map View</th>
            <td>
              <div id="map_search">
                <input type="text" size="70" id="ri-settings-map-search-field" />
                <input type="submit" value="Search" class="button" id="ri-map-search">
              </div>
              <div id="ri-admin-map"></div>
              <span class="description">Position the map and set the zoom level for the default map view.</span>
              <script type="text/javascript" charset="utf-8">
                var m = new google.maps.Map(document.getElementById('ri-admin-map'), {
                  mapTypeId: google.maps.MapTypeId.ROADMAP,
                  center:new google.maps.LatLng(<?php echo $position['lat'] ;?>, <?php echo $position['lng'] ;?>),
                  zoom:<?php echo $position['zoom']; ?>
                });
                
                var geocoder = new google.maps.Geocoder;
                
                var searchmap = function(){
                  var q = jQuery('#ri-settings-map-search-field').val();
                  geocoder.geocode({'address':q, 'bounds': m.getBounds() }, function(results, status){
                    
                    if (status == google.maps.GeocoderStatus.OK) {
                     
                     m.setCenter(results[0].geometry.location);
                     m.setZoom(14);
                      
                    }
                    
                  });
                }
                
                google.maps.event.addListener(m, 'center_changed', function(){
                  var c = m.getCenter();
                  jQuery('[name="riposition[lat]"]').val(c.lat());
                  jQuery('[name="riposition[lng]"]').val(c.lng());
                  
                });
                google.maps.event.addListener(m, 'zoom_changed', function(){
                  jQuery('[name="riposition[zoom]"]').val(m.getZoom());
                });
                
                jQuery('#ri-map-search').click(function(e){
                  e.preventDefault();
                  searchmap();
                })
                
                jQuery('#ri-settings-map-search-field').keypress(function(e){
                  if (e.which == 13) {
                    e.preventDefault();
                    searchmap();
                  };
                })
                
              </script>
            </td>
          </tr>
          <tr>
            <th scope="row">Custom Marker Image</th>
            <td>
              <?php if(ri_use_custom_placemarker()): ?>
              <img src="<?php echo $placemarker; ?>">
              <?php endif; ?>
              <input type="text" name="riplacemarker" value="<?php echo $placemarker; ?>" size="80">
              <span class="description">Image URL to use as map marker.</span>
            </td>
            
          </tr>
        </tbody>
      </table>
      <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ;?>" />
      </p>
    </form>
  </div>
  <?php
}

add_action( 'save_post', 'ri_location_save', 10, 2 );

function ri_location_save($post_id, $post){
  if( !wp_verify_nonce( $_POST['rilocation_nonce'], plugin_basename(__FILE__) ) ) return $post_id;
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
  
  // save the lat/lng
  // save the formatted address
  $ri_location = new RI_Location($post->ID);
  $ri_location->update_properties($_POST);
    
}

function ri_admin_map($post = nil) {
  wp_nonce_field( plugin_basename(__FILE__), 'rilocation_nonce' );
  $meta = get_post_custom($post->ID);
  $ri_location = new RI_Location($post->ID);
?>
<div id="ri-locate-textbox"><input type="text" id="ri-locate-field" /><input type="submit" value="Locate" class="button-primary" id="ri-locate-submit"></div>
<div id="ri-map-control"></div>
<div id="ri-address">
  <input type="hidden" id="ri-lat" name="ri-lat" value="<?php echo $ri_location->lat ;?>" />
  <input type="hidden" id="ri-lng" name="ri-lng" value="<?php echo $ri_location->lng ;?>" />
  <p class="full-width">
    <label for="ri-street-address">Street Address</label><br/>
    <input type="text" size="75" name="ri-street_address" id="ri-street-address" value="<?php echo $ri_location->street_address; ?>" />
  </p>
  <p>
    <label for="ri-locality">City</label><br/>
    <input type="text" name="ri-locality" id="ri-locality" value="<?php echo $ri_location->locality; ?>" />
  </p>
  <p>
    <label for="ri-region-name">State/Region</label><br/>
    <input type="text" name="ri-region" id="ri-region" value="<?php echo $ri_location->region; ?>" />  
  </p>
  <p>
    <label for="ri-region-abbreivation">State/Region Abbr.</label>
    <input type="text" name="ri-region_abbreviation" id="ri-region-abbreviation" value="<?php echo $ri_location->region_abbreviation; ?>" />
  </p>
  <p>
    <label for="ri-postal-code">Postal Code</label>
    <input type="text" name="ri-postal_code" id="ri-postal-code" value="<?php echo $ri_location->postal_code; ?>" />
  </p>
  <p>
    <label for="ri-postal-code">Country Name</label>
    <input type="text" name="ri-country_name" id="ri-country-name" value="<?php echo $ri_location->country_name; ?>" />
  </p>
  <p>
    <label for="ri-postal-code">Country Abbr.</label>
    <input type="text" name="ri-country_abbreviation" id="ri-country-abbreviation" value="<?php echo $ri_location->country_abbreviation; ?>" />
  </p>
</div>
<script type="text/javascript" charset="utf-8">
  (function($){
    var map = new google.maps.Map(document.getElementById("ri-map-control"), {
      zoom:6,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    });

    var geocoder = new google.maps.Geocoder();
    var marker = new google.maps.Marker();

    <?php if ($ri_location->hasLocation()): ;?>
    var initialLocation = new google.maps.LatLng(<?php echo $ri_location->lat ;?>, <?php echo $ri_location->lng ;?>);
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
          var address = {};
          jQuery.each(results[0].address_components, function(){
            address[this.types[0]] = {
             'long' : this.long_name,
             'short' : this.short_name
            }
          });
          if(address['street_number'] && address['route']){            
            $('#ri-street-address').val((address['street_number']['long'] + ' ' + address['route']['long']).trim());
          }else{
            $('#ri-street-address').val('');
          }
          if(address['locality']) $('#ri-locality').val(address['locality']['long']);
          if(address['administrative_area_level_1']){
            $('#ri-region').val(address['administrative_area_level_1']['long']);
            $('#ri-region-abbreviation').val(address['administrative_area_level_1']['short']);
          } 
          if(address['postal_code']) $('#ri-postal-code').val(address['postal_code']['long']);
          if(address['country']){
           $('#ri-country-name').val(address['country']['long']);
           $('#ri-country-abbreviation').val(address['country']['short']); 
          }
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

