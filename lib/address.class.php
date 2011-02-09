<?php

class RI_Location {
  
  var $post_id;
  var $properties = array('lat', 'lng', 'street_address', 'locality', 'region', 'region_abbreviation', 'postal_code', 'country_name', 'country_abbreviation');
  
  function __construct($post_id){
    $this->post_id = $post_id;
    $this->load_post_meta();
  }
  
  function hasLocation(){
    return !(empty($this->lat) || empty($this->lng));
  }
  
  function load_post_meta(){
    $post_meta = get_post_custom($this->post_id);
    foreach($this->properties as $property){
      $this->$property = $post_meta["ri-$property"][0];
    }
  }
  
  function update_properties($properties){
    
    foreach($this->properties as $property){
      $this->$property = $properties["ri-$property"];
    }
    
    $this->save();
    
  }
  
  function save(){
    foreach($this->properties as $property){
      update_post_meta($this->post_id, "ri-$property", $_POST["ri-$property"]);     
    }
  }
  
}