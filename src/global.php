<?php

$bleech_template_data_d = array();
$bleech_template_data_area = array();

if(!function_exists('d')) {
  function d($key, $data = null) {
    if(empty($data)) {
      global $bleech_template_data_d;
      $data = $bleech_template_data_d;
    }
    $output = '';
    if($key === '*') {
      $output = $data;
    } elseif(isset($data) && is_array($data) && array_key_exists($key, $data)) {
      $output = $data[$key];
    }

    return $output;
  }
}

if(!function_exists('area')) {
  function area($key) {
    global $bleech_template_data_area;
    $data = $bleech_template_data_area;
    $output = '';
    if($key === '*') {
      $output = $data;
    } elseif(isset($data) && is_array($data) && array_key_exists($key, $data)) {
      $output = $data[$key];
    }

    return $output;
  }
}

if(!function_exists('h')) {
  function h($type, $options = null, $data = null) {
    $output = '';
    switch ($type) {
      case 'picture':
        $output = "<picture><!--[if IE 9]><video style='display: none;'><![endif]-->";
        if(array_key_exists('alt', $options)) {
          $alt = $options['alt'];
        } else {
          $alt = '';
        }
        foreach($options['sizes'] as $size => $media_query) {
          $output .= '<source srcset="' . $data['sizes'][$size]. '" media="' . $media_query .'">';
        }
        $output .= '<!--[if IE 9]></video><![endif]-->';
        $output .= '<img srcset="' . $data['sizes'][$options['default']] . '" alt="' . $alt . '">';
        $output .= '</picture>';
        break;
      case 'svg':
        $file_path = get_template_directory() . '/assets/images/svg/' . $options['file_name'] . '.svg';
        if(array_key_exists('file_name', $options) && file_exists($file_path)) {
          $output = file_get_contents($file_path);
        }
        break;
    }
    return $output;
  }
}

if(!function_exists('requireAsset')) {
  function requireAsset($assetPath) {
    $templateDirectory = get_template_directory();
    $templateDirectoryUri = get_template_directory_uri();
    $manifestPath = $templateDirectory . '/rev-manifest.json';
    if(file_exists($manifestPath)) {
      $assetData = json_decode(file_get_contents($manifestPath), true);
      if(array_key_exists($assetPath, $assetData)) {
        $assetPath = $assetData[$assetPath];
      }
    }
    return $assetPath;
  }
}

if(!function_exists('requireAssetUrl')) {
  function requireAssetUrl($assetPath) {
    $templateDirectoryUri = get_template_directory_uri();
    $assetPath = requireAsset($assetPath);
    $assetUrl = $templateDirectoryUri . '/' . $assetPath;
    return $assetUrl;
  }
}
