<?php

namespace Bleech\Module;

use Bleech\Module\DefaultDataSetter;
use Bleech\Module\DataSetter;
use Bleech\Config;

class ConstructionPlanBuilder {
  public static function fromConfig($config, $parentData = []) {
    $result = [];
    foreach ($config as $areaName => $modules) {
      foreach ($modules as $moduleName => $moduleConfig) {
        if(!isset($result[$areaName])) {
          $result[$areaName] = [];
        }
        if(isset($moduleConfig['acf_content']) && $moduleConfig['acf_content']) {
          $acfModules = self::fromFlexibleContent($areaName, $moduleConfig, $parentData);
          $result[$areaName] = array_merge($result[$areaName], $acfModules);
        } else {
          $preparedModule = self::prepareModule($areaName, $moduleConfig, $parentData);
          if($preparedModule['iterate_over']) {
            $iteratedModules = self::fromIterateableModule($areaName, $moduleConfig);
            $result[$areaName] = array_merge($result[$areaName], $iteratedModules);
          } else {
            $result[$areaName][$moduleName] = self::fromPreparedModule($preparedModule);
          }
        }
      }
    }
    return $result;
  }

  protected static function prepareModule($areaName, $config, $parentData) {
    $config = DefaultDataSetter::setDefaults($config);
    $config = DataSetter::setData($areaName, $config, $parentData);
    $config = self::setModuleTemplatePath($config);
    return $config;
  }

  protected static function fromFlexibleContent($areaName, $config = [], $parentData = []) {
    $modules = [];
    # TODO: enable data store in acf_content
    if(isset($parentData['d'][$areaName]) && is_array($parentData['d'][$areaName])) {
      $modules = array_map(function($dynamicModuleData) {
        return [
          'name' => $dynamicModuleData['acf_fc_layout'],
          'data' => ['d' => $dynamicModuleData]
        ];
      }, $parentData['d'][$areaName]);
    }
    if(!empty($modules)) {
      $modules = self::fromConfig([$modules])[0];
    }
    return $modules;
  }

  protected static function fromIterateableModule($areaName, $config) {
    $iterateKey = $config['iterate_over'];
    $iterateData = $config['data']['d'][$iterateKey];
    $modules = array_map(function($data) {
      $config['data']['d'] = $data;
      $preparedModule = self::prepareModule($areaName, $config, []);
      return self::fromPreparedModule($prepareModule);
    }, $iterateData);
    return $modules;
  }

  protected static function fromPreparedModule($module) {
    if(isset($module['modules'])) {
      $config = $module['modules'];
      $data = $module['data'];
      $module['modules'] = self::fromConfig($config, $data);
    }
    return $module;
  }

  protected static function setModuleTemplatePath($config) {
    $config['path'] = Config\MODULE_PATH . $config['name'] . '/' . $config['view'] . '.php';
    return $config;
  }
}
