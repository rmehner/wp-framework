<?php

namespace Bleech;

use Bleech\Module\ConstructionPlanBuilder;
use Bleech\Module\Renderer;

class Modules {

  public $config = array();
  protected $config_directory = '/bleech/app/config/';
  protected $config_file_path = '';
  protected $config_file_name = '';
  public $set_construction_plan_html;

  function __construct($config, $set_construction_plan_html = false) {
    $this->config = $config;
    $this->set_construction_plan_html = $set_construction_plan_html;

    $data = array();
    $this->construction_plan = ConstructionPlanBuilder::fromConfig(array($this->config), $data);

    $rendered_html = Renderer::renderModules($this->construction_plan);

    $this->module_html = array_key_exists(0, $rendered_html) ? $rendered_html[0] : '';

    $this->add_construction_plan_to_html();
  }

  public function render() {
    return $this->module_html;
  }

  protected function add_construction_plan_to_html() {
    $construction_plan = $this->strip_construction_plan($this->construction_plan);
    $script = "<body$1><script>
        var constructionPlan = " . json_encode(array_key_exists(0, $construction_plan) ? $construction_plan[0] : array()) . ";
      </script>
    ";
    $this->module_html = preg_replace('/<body(.*?)>/is', $script, $this->module_html);
  }

  protected function strip_construction_plan($construction_plan) {
    return array_map(function($area) {
      return array_map(function($module) {
        unset($module['data']);
        unset($module['path']);
        if(array_key_exists('modules', $module)) {
          $module['modules'] = $this->strip_construction_plan($module['modules']);
        }
        return $module;
      }, $area);
    }, $construction_plan);
  }
}
