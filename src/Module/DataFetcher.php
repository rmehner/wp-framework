<?php

namespace Bleech\Module;

class DataFetcher {
  public static function fetch($config = array(), $parentData = array()) {
    if(isset($config['data_store'])) {
      $data = self::getDataFromDataStore($config['data_store'], $config['data_store_args']);
      $data = self::getSinglePostData($config, $data);
    } elseif(isset($config['data']) && isset($config['data']['d']) && !empty($config['data']['d'])) {
      $data = $config['data']['d'];
    } elseif(isset($config['custom_data'])) {
      $data = $config['custom_data'];
      unset($config['custom_data']);
    } else {
      $data = isset($parentData['d']) ? $parentData['d'] : $parentData;
    }
    if(isset($config['data_mapping'])) {
      $data = self::mapData($data, $config['data_mapping']);
    }
    return $data;
  }

  protected static function getDataFromDataStore($dataStoreCmd, $dataStoreArgs) {
    if(is_string($dataStoreCmd)) {
      list($dataStoreName, $cmd) = explode('#', $dataStoreCmd);
    } else {
      $dataStoreName = $dataStoreCmd['store'];
      $cmd = $dataStoreCmd['cmd'];
    }
    # TODO: make data stores use namespaces
    $dataStoreName = "\\App\\DataStores\\${dataStoreName}";
    $dataStore = new $dataStoreName();
    # TODO: make fetch method return data instead of writing to ->data
    $dataStore->fetch($cmd, $dataStoreArgs);
    return $dataStore->data[$cmd];
  }

  protected static function getSinglePostData($config, $data) {
    if(!$config['iterate_over'] && !$config['multi_posts'] && isset($data['posts'])) {
      $data = $data['posts'][0];
    }
    return $data;
  }

  protected static function mapData($data, $mapping) {
    foreach($mapping as $from => $to) {
      if(isset($data[$from])) {
        $data[$to] = $data[$from];
        unset($data[$from]);
      }
    }
    return $data;
  }
}
