<?php

namespace Bleech;

use Bleech\Config;

class ConfigLoader {

  public static function fromTemplateName($templateName) {
    $configName = self::configNameFromTemplateName($templateName);
    return self::fromConfigName($configName);
  }

  protected static function configNameFromTemplateName($templateName) {
    $routesConfigPath = Config\CONFIG_PATH . 'routes.json';
    $routesConfig = file_get_contents($routesConfigPath);
    $routesConfig = json_decode($routesConfig, true);
    $routes = $routesConfig['routes'];
    if(isset($routes[$templateName])) {
      if(is_array($routes[$templateName])) {
        $configName = $routes[$templateName]['config'];
      } else {
        $configName = $routes[$templateName];
      }
    }
    # TODO: handle config not found
    return $configName;
  }

  protected static function fromConfigName($configName) {
    $configPath = Config\CONFIG_PATH . 'templates/' . $configName . '.json';
    if(file_exists($configPath)) {
      $config = file_get_contents($configPath);
      $config = json_decode($config, true);
    }
    return $config;
  }
}
