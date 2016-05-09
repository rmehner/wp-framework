<?php

namespace Bleech;

  // $bleech_includes = array(
  //   'jadeRuntime.php',                   // Jade Runtime
  //   'Options.php',                       // Options
  //   // 'App/Options.php',                        // Options
  //   'Actions.php',                       // Actions
  //   // 'App/Actions.php',                        // Actions
  //   'Helper.php',                        // Helper
  //   'global.php',                        // Global Template Data
  //   'Base.php',                          // Base
  //   'DataStore.php',                     // Data Store
  //   'Modules.php',                       // Modules
  //   // 'App/Acf.php',                            // ACF
  //   'Autoload.php',                      // Autoload Modules helper.php
  //   'CustomPostTypeLoader.php',          // Custom Post Type Loader
  //   'FieldLoader.php',                   // ACF fieldLoader
  //   'Wordpress/Globals/templates.php'
  // );
  //
  // foreach ($bleech_includes as $file) {
  //   if (!$filepath = locate_template($file)) {
  //     trigger_error(sprintf(__('Error locating %s for inclusion', 'roots'), $file), E_USER_ERROR);
  //   }
  //   require_once $filepath;
  // }
  // unset($file, $filepath);

  new Autoload();
  // App\Options\registerAll();
  new FieldLoader();
  Actions\registerAll();
  // App\Actions\registerAll();
