<?php

namespace Bleech;

class Autoload
{
  private $bleech_app_path = '';
  private $modules_path = '/Modules';

  function __construct() {
    $this->bleech_app_path = get_template_directory();
    $this->load_modules();
  }

  function load_modules() {
    $class_root = $this->bleech_app_path . $this->modules_path;
    $directories = new \RecursiveDirectoryIterator($class_root);

    foreach(new \RecursiveIteratorIterator($directories) as $file) {
      if($file->isFile() && ($file->getFilename() == 'Helper.php' || $file->getFilename() == 'hooks.php')){
        $full_path = $file->getRealPath();
        require_once $full_path;
      }
    }
    unset($file, $full_path);
  }
}
