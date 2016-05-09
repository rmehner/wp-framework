<?php

namespace Bleech\Options;

function add_acf_option_pages($option_pages) {
  if(function_exists('acf_add_options_page')) {
    foreach ($option_pages as $option_page) {
      acf_add_options_page($option_page);
    }
  }
}

function add_acf_option_subpages($option_subpages) {
  if(function_exists('acf_add_options_page')) {
    foreach ($option_subpages as $options_subpage) {
      acf_add_options_sub_page($options_subpage);
    }
  }
}
