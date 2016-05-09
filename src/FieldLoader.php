<?php

namespace Bleech;

use Bleech\Config;
use Bleech\Helper;

class FieldLoader {
  function __construct() {
    if(function_exists('acf_add_local_field_group')) {
      $this->getConfigs();
      $this->resolveModuleConfigs();
      $this->resolveFieldConfig();
      $this->resolveGroupConfigs();
      $this->registerFields();
    }
  }

  private function getConfigs() {
    $this->fieldConfig = json_decode(file_get_contents(Config\CONFIG_PATH . 'fields/fields.json'));
    $this->groupConfig = json_decode(file_get_contents(Config\CONFIG_PATH . 'fields/groups.json'));
    $this->locationConfig = json_decode(file_get_contents(Config\CONFIG_PATH . 'fields/locations.json'));
    $this->modulesConfig = [];
    foreach(scandir(Config\MODULE_PATH) as $moduleName) {
      if($moduleName !== '.' && $moduleName !== '..') {
        $modulePath = Config\MODULE_PATH . $moduleName;
        if(is_dir($modulePath) && file_exists($modulePath . '/fields.json')) {
          $this->modulesConfig[$moduleName] = json_decode(file_get_contents($modulePath . '/fields.json'));
        }
      }
    }
  }

  private function resolveModuleConfigs() {
    $this->modulesConfig = array_map(function($config) {
      return $this->resolveConfigFields($config->layout, $config->fields);
    }, $this->modulesConfig);
  }

  private function resolveConfigFields($config, $repo, $prefix = NULL, $recursive = true) {
    if(!empty($config->fields)) $config->fields = $this->resolveConfig($config->fields, $repo, $prefix, $recursive);
    if(!empty($config->sub_fields)) $config->sub_fields = $this->resolveConfig($config->sub_fields, $repo, $prefix, $recursive);
    if(!empty($config->layouts)) $config->layouts = $this->resolveConfig($config->layouts, $repo, $prefix, $recursive);
    return $config;
  }

  private function resolveConfig($config, $repo, $prefix, $recursive) {
    $repo = (array)$repo;
    return array_map(function($fieldName) use ($repo, $prefix, $recursive) {
      if(!is_string($fieldName)) {
        $output = $fieldName;
        $fieldName = Helper\toCamelCase($fieldName->name);
      } else {
        $output = $repo[$fieldName];
      }
      if(empty($output)) {
        $globalModule = unserialize(serialize($this->modulesConfig[$fieldName]));
        if(empty($globalModule)) {
          trigger_error("Cannot load config for field $fieldName. Trace key $prefix.");
        } else {
          if($prefix) $newPrefix = $prefix . '_' . $fieldName;
          $output = $this->resolveConfigFields($globalModule->layout, $globalModule->fields, $newPrefix);
        }
      }
      if($prefix) {
        $newPrefix = $prefix . '_' . $fieldName;
        $output->key = 'field_' . $newPrefix;
      }
      if($recursive) $output = $this->resolveConfigFields($output, $repo, $newPrefix, $recursive);
      return $output;
    }, $config);
  }

  private function resolveFieldConfig() {
    $this->fieldConfig = array_map(function($config) {
      if(!empty($config->layouts)) $config->layouts = $this->resolveConfig($config->layouts, $this->modulesConfig, '', false);
      return $config;
    }, (array)$this->fieldConfig);
  }

  private function resolveGroupConfigs() {
    $fieldConfig = $this->fieldConfig;
    $locationConfig = $this->locationConfig;
    $modulesConfig = $this->modulesConfig;
    $this->groupConfig = array_map(function($config, $key) use ($fieldConfig, $locationConfig) {
      $config = $this->resolveConfigFields($config, $fieldConfig, $key);

      $config->location = array_map(function($location) use ($locationConfig){
        return $this->resolveConfig($location, $locationConfig, null, false);
      }, (array)$config->location);
      $config->key = 'group_' . $key;
      return $config;
    }, (array)$this->groupConfig, array_keys((array)$this->groupConfig));
  }

  private function registerFields() {
    foreach((array)$this->groupConfig as $group) {
      $group = Helper\objectToArray($group);
      acf_add_local_field_group($group);
    }
  }
}
