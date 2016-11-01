<?php

namespace WPStarter;

class ConstructionPlan {
  public static function fromConfig($config) {
    return new self($config);
  }

  public static function fromConfigFile($configName) {
    $configPath = apply_filters('WPStarter/configPath', $configName);
    $config = json_decode(file_get_contents($configPath), true);
    return new self($config);
  }

  function __construct($config) {
    $this->config = $config;
  }

  public function toArray() {
    return $this->config;
  }
}
