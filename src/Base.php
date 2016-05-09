<?php

namespace Bleech;

class Base
{
  function __construct($templateName) {
    global $template;
    global $post;

    # TODO: move somewhere else
    if(function_exists('get_global_option')) {
      if(get_global_option('maintenance_enabled')) {
        $currentUrl = get_the_permalink();
        $maintenanceUrl = get_global_option('maintenance_page');
        if($currentUrl !== $maintenanceUrl) {
          wp_redirect(get_global_option('maintenance_page'));
          exit;
        }
      }
    }

    $pageConfig = ConfigLoader::fromTemplateName($templateName);
    $modules = new Modules($pageConfig, false);
    echo $modules->render();
  }
}
