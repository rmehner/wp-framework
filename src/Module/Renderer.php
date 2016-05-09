<?php

namespace Bleech\Module;

use Bleech\Config;
use Bleech\Module\DataFetcher;
use Bleech\Module\DefaultDataSetter;

class Renderer {
  public static function renderModules($config) {
    $result = array();
    foreach ($config as $areaName => $modules) {
      $html = '';
      foreach ($modules as $moduleName => $moduleConfig) {
        if(isset($moduleConfig['modules'])) {
          $moduleConfig['data']['area'] = self::renderModules($moduleConfig['modules']);
        }
        $html .= self::renderModule($moduleConfig);
      }
      $result[$areaName] = $html;
    }
    return $result;
  }

  protected static function renderModule($config) {
    return self::renderFile($config['data'], $config['path']);
  }

  protected static function renderModuleSimple($moduleName, $data = array()) {
    $config = array();
    $config['name'] = $moduleName;
    $config = DefaultDataSetter::setDefaults($config);
    # TODO: execute helper
    $composedData = array(
      'd' => $data,
      'area' => array()
    );
    $path = Config\MODULE_PATH . $moduleName . '/' . $config['view'] . '.php';
    $html = self::renderFile($composedData, $path);
    return $html;
  }

  protected static function renderModuleWithConfig($moduleName, $parentData, $config = array()) {
    $config['name'] = $moduleName;
    $config = DefaultDataSetter::setDefaults($config);
    $data = DataFetcher::fetch($config, $parentData);
    $composedData = array(
      'd' => $data,
      'area' => array()
    );
    $path = Config\MODULE_PATH . $moduleName . '/' . $config['view'] . '.php';
    $html = self::renderFile($composedData, $path);
    var_dump($data);
    var_dump($html);
  }

  protected static function renderFile($data, $filePath) {
    global $bleech_template_data_d;
    global $bleech_template_data_area;

    $bleech_template_data_d = $data['d'];
    $bleech_template_data_area = $data['area'];

    $output = '';
    if(file_exists($filePath)) {
      extract($data, EXTR_SKIP);
      ob_start();
      include $filePath;
      $output = ob_get_contents();
      ob_get_clean();
    } /*else if (current_user_can('manage_options')) {
      new Log("render module file failed: module file doesn´t exist: $filePath", null, 'console.error');
    }*/
    return $output;
  }
}
