<?php

namespace Bleech\Actions;

define(__NAMESPACE__ . '\NS', __NAMESPACE__ . '\\');

function registerAll() {
  add_action('init', ['\\Bleech\\CustomPostTypeLoader', 'registerCustomPostTypes']);
  add_action('init', NS . 'remove_default_editor');
  add_action('init', NS . 'register_bleech_shortcodes');
  add_action('admin_enqueue_scripts', NS . 'load_admin_vendor_style');
  add_action('wp_ajax_update_bleech_templates', NS . 'update_bleech_templates_callback');
  add_action('wp_ajax_get_all_posts', NS . 'get_all_posts_callback');
  add_action('admin_notices', NS . 'local_installation');
}

function remove_default_editor() {
  remove_post_type_support('page', 'editor');
  remove_post_type_support('post', 'editor');
}

function module_shortcodes($attr) {
  $bh = new Bleech_App_Helper();
  $module = array(
    'name' => $attr['type'],
    'data' => array(
      'd' => array(),
      'area' => null
    )
  );
  $module_class = $bh->toCamelCase($module['name']);
  $helper = 'Bleech_' . $module_class . '_Helper';
  if(class_exists($helper)) {
    $new_data = $helper::add_data(array());
  } else {
    $new_data = $module['data']['d'];
  }
  $module['data']['d'] = $new_data;
  global $bleech_template_data_d;

  $bleech_template_data_d = $module['data']['d'];

  $file_path = get_template_directory() . '/bleech/app/modules/' . $attr['type'] . '/index.php';
  ob_start();
  include $file_path;
  $output = ob_get_contents();
  ob_get_clean();
  return $output;
}

function register_bleech_shortcodes(){
   add_shortcode('bleech-module', array($this, 'module_shortcodes'));
}

function load_admin_vendor_style($hook) {
  if ('bleech-app_page_app_info_page' != $hook) {
    return;
  }
  wp_enqueue_style( 'admin_vendor_css', get_template_directory_uri() . '/assets/styles/admin-vendor.css', false, '1.0.0' );
  wp_enqueue_style( 'admin_styles_css', get_template_directory_uri() . '/assets/styles/admin-styles.css', false, '1.0.0' );
  wp_enqueue_script('admin_vendor_js', get_template_directory_uri() . '/assets/scripts/admin-vendor.js', array(), null, true);
  wp_enqueue_script('admin_main_js', get_template_directory_uri() . '/assets/scripts/admin-main.js', array(), null, true);
}

function update_bleech_templates_callback() {
  $bleech_appinfo = new Bleech_Appinfo();
  $bleech_appinfo->update_posts_templates();
  $data = array(
    'status'=> 'success'
  );
  echo json_encode($data);
  die();
}

function get_all_posts_callback() {
  $bleech_appinfo = new Bleech_Appinfo();
  echo json_encode($bleech_appinfo->get_post_list());
  die();
}

function local_installation() {
  $url = parse_url($_SERVER['HTTP_REFERER']);
  $host = $url['host'];
  if($host === 'localhost' || strpos($host, '.dev') || ip2long($host)) {
    $class = 'error';
    $message = 'You are using a local installation of wordpress. If you make any changes be sure to add them to the staging evironment. Also make sure that you have cloned the current staging environment to your local installation.';
    $command = 'Run <code>npm run cloneStaging</code> to update your local db and assets with the current staging environment.';
    echo "<div class=\"$class\"> <p>$message</p><p>$command</p></div>";
  }
}
