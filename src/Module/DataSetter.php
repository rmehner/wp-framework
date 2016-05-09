<?php

namespace Bleech\Module;

use Bleech\Module\DataFetcher;
use Bleech\Module\Counter;

class DataSetter {
  public static function setData($areaName, $config, $parentData) {
    $data = DataFetcher::fetch($config, $parentData);
    unset($config['data_store']);
    $config['data']['d'] = $data;
    $config = self::setBaseClasses($areaName, $config);
    $config = self::addDataFromHelper($config, $parentData);
    return $config;
  }

  protected static function setBaseClasses($areaName, $config) {
    $moduleId = Counter::next();
    $moduleClass = self::toCssClass($config['name']);
    $areaClass = 'area--' . self::toCssClass($areaName);
    $baseClassesArray = ['modules', $areaClass, 'module--' . $moduleId];
    if(isset($config['cssClass'])) {
      $baseClassesArray[] = $config['cssClass'];
    } else {
      $baseClassesArray[] = $moduleClass;
    }
    $baseClasses = join($baseClassesArray, ' ');
    $config['data']['d']['base-classes'] = $baseClasses;
    $config['id'] = $moduleId;
    return $config;
  }

  protected static function addDataFromHelper($config, $parentData) {
    $moduleClass = self::toCamelCase($config['name']);
    $helper = "\\Modules\\${moduleClass}\\Helper";
    if(function_exists($helper . '\\add_data')) {
      $parentData = isset($parentData['d']) ? $parentData['d'] : [];
      $addData = $helper . '\\add_data';
      $data = $addData($config['data']['d'], $config, $parentData);
      $config['data']['d'] = $data;
    }
    if(function_exists($helper . '\\addModules')) {
      $modules = isset($config['modules']) ? $config['modules'] : [];
      $addModules = $helper . '\\addModules';
      $modules = $addModules($modules, $config['data']['d']);
      if(!empty($modules)) {
        $config['modules'] = $modules;
      }
    }
    return $config;
  }

  # TODO: put in namespaced helper
  protected static function toCssClass($string) {
    $str = str_replace(' ', '-', $string);
    $str = str_replace('_', '-', $str);
    return $str;
  }

  # TODO: put in namespaced helper
  protected static function toCamelCase($string, $capitalizeFirstCharacter = true) {
    $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));

    if (!$capitalizeFirstCharacter) {
        $str[0] = strtolower($str[0]);
    }
    return $str;
  }
}
