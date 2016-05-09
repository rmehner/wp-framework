<?php

namespace Bleech;

class Application
{
  function __construct() {
    $files = [
      "config.php",
      "helper.php",
      "jadeRuntime.php",
      "global.php",
      "Wordpress/Globals/templates.php",
      "actions.php",
      "options.php",
      "init.php",
    ];
    foreach($files as $file) {
      require_once dirname(__FILE__) . '/' . $file;
    }
  }
}
