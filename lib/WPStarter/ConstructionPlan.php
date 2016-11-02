<?php

namespace WPStarter;

use Exception;

class ConstructionPlan {
  public static function fromConfig($config) {
    if (!array_key_exists('name', $config)) {
      throw new Exception('No Module specified.');
    }
    $config['data'] = [];
    $config['areaHtml'] = [];
    unset($config['dataFilter']);
    return $config;
  }
  //
  // public static function fromConfigFile($configName) {
  //   $configPath = apply_filters('WPStarter/configPath', $configName);
  //   $config = json_decode(file_get_contents($configPath), true);
  //   return new self($config);
  // }
  //
  // function __construct($config) {
  //   $this->config = $config;
  // }
  //
  // public function toArray() {
  //   return $this->config;
  // }
}
