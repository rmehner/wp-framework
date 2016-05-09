<?php

namespace Bleech\Wordpress\Globals;

function setCurrentTemplate($template = 'base.php') {
  $GLOBALS['currentTemplate'] = basename($template, '.php');
  return $template;
}
add_filter('template_include', 'Bleech\\Wordpress\\Globals\\setCurrentTemplate', 1000);
