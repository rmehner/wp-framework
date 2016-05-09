<?php

namespace Bleech\Module;

class DefaultDataSetter {
  public static function setDefaults($config) {
    if(!array_key_exists('view', $config)) {
      $config['view'] = 'index';
    }
    if(!array_key_exists('path', $config)) {
      $config['path'] = '';
    }
    if(!array_key_exists('iterate_over', $config)) {
      $config['iterate_over'] = false;
    }
    if(!array_key_exists('multi_posts', $config)) {
      $config['multi_posts'] = false;
    }
    if(!array_key_exists('data_store_args', $config)) {
      $config['data_store_args'] = [];
    }
    if(!array_key_exists('data', $config)) {
      $config['data'] = array(
        'd' => array(),
        'area' => array()
      );
    } elseif(!isset($config['data']['area'])) {
      $config['data']['area'] = array();
    }
    return $config;
  }
}
