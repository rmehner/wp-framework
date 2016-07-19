<?php

namespace Bleech\Helper;

function pprint($data) {
  echo '<pre>';
  print_r($data);
  echo '</pre>';
}

function map_keys(array $arr, array $map) {
  //we'll build our new, rekeyed array here:
  $newArray = array();
  //iterate through the source array
  foreach($arr as $oldKey => $value) {
    //if the old key has been mapped to a new key, use the new one.
    //Otherwise keep the old key
    $newKey = isset($map[$oldKey]) ? $map[$oldKey] : $oldKey;
    //add the value to the new array with the "new" key
    $newArray[$newKey] = $value;
  }
  return $newArray;
}

function remove_post_prefix_from_keys(array $arr) {
  $newArray = array();
  foreach($arr as $oldKey => $value) {
    $newArray[str_replace('post_', "", $oldKey)] = $value;
  }
  return $newArray;
}

function get_echo_function($func_name, $args = null) {
  ob_start();
  if(isset($args)) {
    $func_name($args);
  } else {
    $func_name();
  }
  $output = ob_get_contents();
  ob_get_clean();
  return $output;
}

function get_attributes_array($string) {
  $output = array();
  $attributes = explode(" ", $string);
  foreach ($attributes as $attribute_string) {
    $attribute = explode("=", $attribute_string);
    $output[$attribute[0]] = str_replace('"', "", $attribute[1]);
  }
  return $output;
}

function toCamelCase($string, $capitalizeFirstCharacter = true) {
  $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
  $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));

  if (!$capitalizeFirstCharacter) {
      $str[0] = strtolower($str[0]);
  }
  return $str;
}

function toCssClass($string) {
  $str = str_replace(' ', '-', $string);
  $str = str_replace('_', '-', $str);
  return $str;
}


function strstartswith($haystack, $needle) {
  if (!$needle) return false;
  return strpos($haystack, $needle) === 0;
}

function objectToArray($obj) {
  if(is_object($obj)) $obj = (array) $obj;
  if(is_array($obj)) {
    $new = array();
    foreach($obj as $key => $val) {
      $new[$key] = objectToArray($val);
    }
  } else {
    $new = $obj;
  }
  return $new;
}
