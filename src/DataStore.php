<?php

namespace Bleech;

use Bleech\Helper;
$bleech_acf_fields_queried = false;

class DataStore
{
  public $data = array();

  function __construct() {
    // $this->bh = new Bleech_App_Helper();
  }

  public function fetch($func_name, $args = []) {
    if(!array_key_exists($func_name, $this->data)) {
      if (method_exists($this, $func_name)) {
        call_user_func_array(array(&$this, $func_name), $args);
      } else {
        $this->data[$func_name] = array('error' => "data['$func_name'] not found");
      }
    }
    return $this->data[$func_name];
  }

  public function query_wp_posts($args = array(), $map = array()) {
    $caller_function = debug_backtrace();
    $data_store_key = $caller_function[1]['function'];

    $new_data = array();
    if(count($args) == 0) {
      global $wp_query;
      $query = $wp_query;
    } else {
      $query = new \WP_Query( $args );
    }

    $posts = $query->posts;

    $posts = array_map(function($post) {
      return (array)$post;
    }, $posts);

    $posts = $this->addAdditionalDefaultData($posts);

    foreach ($posts as $post_key => $post) {
      if(is_array($post)) {
        $new_data[$post_key] = Helper\map_keys(
          $post,
          $map
        );
      }
    }

    $this->data[$data_store_key] = array(
      'posts' => $new_data,
      'meta' => array(
        'max_num_pages' => $query->max_num_pages,
        'current_page'  => max(1, $query->query_vars['paged'])
      )
    );
    wp_reset_query();

    return $this->data[$data_store_key];
  }

  public function wp_base() {
    $this->data['wp_base'] = array(
      'lang' => get_bloginfo('language'),
      'feed-name' => get_bloginfo('name') . " " . __("Feed"),
      'feed-href' => esc_url(get_feed_link()),
      'body-class' => join(' ', get_body_class()),
      'apple-touch-icon-180x180-path' => get_template_directory_uri() . '/apple-touch-icon-180x180.png',
      'favicon-path' => get_template_directory_uri() . '/favicon.png'
    );

    if(is_rtl()){
      $this->data['wp_base']['dir'] = 'rtl';
    } else {
      $this->data['wp_base']['dir'] = 'ltr';
    }
  }

  public function _get_data_from_acf($id = null) {
    global $bleech_acf_fields_queried;

    if(!$bleech_acf_fields_queried) {
      $bleech_acf_fields_queried = true;
      $query = new \WP_Query( array(
        'post_type' => array('acf-field-group', 'acf-field'),
        'posts_per_page' => -1,
        'post_status' => 'any'
      ) );
      wp_reset_query();
    }
    if(empty($id)) {
      return null;
    }
    $fields = get_fields($id);
    return empty($fields) ? [] : $fields;
  }

  public function addAdditionalDefaultData($posts) {
    $this->_cache_post_images($posts);

    $posts = array_map(function($post) {
      $post['feature_image'] = $this->_add_feature_image($post);
      $post['post_category'] = $this->_add_category($post);
      $post['post_tags'] = $this->_add_tags($post);
      $post['post_url'] = $this->_add_permalink($post);
      $post['post_date'] = $this->_add_date($post, array(
        'key' => 'post_date',
        'fmt' => '%d. %B %Y'
      ));
      $post = array_merge($post, $this->_get_data_from_acf($post['ID']));
      return $post;
    }, $posts);

    return $posts;
  }

  public function query_extra_data($data_key, $mapping, $caches = array()) {
    if(is_string($data_key)) {
      $posts =& $this->data[$data_key]['posts'];
    } else {
      $posts =& $data_key['posts'];
    }
    if(!empty($caches)) {
      foreach($caches as $cache) {
        $func = '_cache_' . $cache;
        $this->$func($posts);
      }
    }
    if(is_array($posts)) {
      foreach($posts as &$post) {
        foreach($mapping as $key => $val) {
          if(is_string($val)) {
            $func = '_add_' . $val;
            $post[$key] = $this->$func($post);
          } else {
            $func = '_add_' . $val['func'];
            $post[$key] = $this->$func($post, $val['args']);
          }
        }
      }
    }
    return $posts;
  }

  protected function _cache_post_images($posts) {
    $ids = array();
    if(is_array($posts)) {
      foreach($posts as &$post) {
        $post_id = $post['ID'];
        if(has_post_thumbnail($post_id)) {
          $ids[] = (int)get_post_thumbnail_id( $post_id );
        }
      }
      new \WP_Query( array(
        'post_type' => 'attachment',
        'posts_per_page' => -1,
        'post_status' => 'any',
        'post__in' => $ids
      ) );
      wp_reset_query();
    }
  }

  protected function _add_permalink($post) {
    return get_permalink($post['ID']);
  }

  protected function _add_author_name($post) {
    return get_the_author_meta('display_name', $post['post_author']);
  }

  protected function _add_feature_image($post) {
    return $this->_getPictureElementPostThumbnail($post['ID'], array('thumbnail'));
  }

  protected function _add_category($post) {
    $categories = get_the_category($post['ID']);
    if(is_array($categories)) {
      return array_map(function($category) {
        $category = (array) $category;
        $category['link'] = get_category_link($category['term_id']);
        return $category;
      }, $categories);
    } else {
      return array();
    }
  }

  protected function _add_tags($post) {
    $tags = get_the_tags($post['ID']);
    if(is_array($tags) && !empty($tags)) {
      return array_map(function($tag) {
        $tag = (array) $tag;
        $tag['link'] = get_tag_link($tag['term_id']);
        return $tag;
      }, $tags);
    } else {
      return array();
    }
  }

  protected function _add_date($post, $args) {
    return utf8_encode(strftime($args['fmt'], strtotime($post[$args['key']])));
  }

  protected function _getPictureElementPostThumbnail($post_id) {
    if(has_post_thumbnail($post_id)) {
      $post_thumbnail_id = get_post_thumbnail_id( $post_id );
      return acf_get_attachment($post_thumbnail_id);
    } else {
      return array();
    }
  }
}
