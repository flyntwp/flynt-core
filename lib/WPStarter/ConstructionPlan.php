<?php

namespace WPStarter;

use Exception;

class ConstructionPlan {
  public static function fromConfig($config, $parentData = []) {
    // Check configuration for errors
    if(!is_array($config)) {
      throw new Exception('Config needs to be an array! ' . gettype($config) . ' given.');
    }
    if (!array_key_exists('name', $config)) {
      throw new Exception('No Module specified.');
    }

    // add data to module
    $config['data'] = [];
    $config = self::applyDataFilter($config);
    $config = self::addCustomData($config);

    // add submodules (dynamic + static)
    $config = self::addSubmodules($config, $parentData);

    // return cleaned up construction plan for the current module
    return self::cleanModule($config);
  }

  public static function fromConfigFile($configName) {
    $configPath = apply_filters('WPStarter/configPath', $configName);
    if (!is_file($configPath)) {
      throw new Exception('Config file not found: ' . $configPath);
    }
    $config = apply_filters('WPStarter/configFileLoader', null, $configPath);
    if (is_null($config)) {
      $config = json_decode(file_get_contents($configPath), true);
    }
    return self::fromConfig($config);
  }

  protected static function applyDataFilter($config) {
    if (array_key_exists('dataFilter', $config)) {
      $args = [ $config['data'] ];
      if (array_key_exists('dataFilterArgs', $config)) {
        $args = array_merge($args, $config['dataFilterArgs']);
      }
      $config['data'] = apply_filters_ref_array($config['dataFilter'], $args);
    }
    return $config;
  }

  protected static function addCustomData($config) {
    if (array_key_exists('customData', $config)) {
      // custom data overwrites original data
      $config['data'] = array_merge($config['data'], $config['customData']);
    }
    return $config;
  }

  protected static function addSubmodules($config, $parentData) {
    // add dynamic submodules to areas
    $areas = array_key_exists('areas', $config) ? $config['areas'] : [];
    $config['areas'] = apply_filters("WPStarter/dynamicSubmodules?name={$config['name']}", $areas, $config['data'], $parentData);

    // iterate areas and recursively map child module construction plan
    if (!empty($config['areas'])) {
      $config['areas'] = array_map(function($modules) use ($config, $parentData) {
        return self::mapAreaModules($modules, $config, $parentData);
      }, $config['areas']);
    }
    return $config;
  }

  protected static function mapAreaModules($modules, $config, $parentData) {
    return array_map(function($module) use ($config, $parentData) {
      $data = empty($config['data']) ? $parentData : $config['data'];
      return self::fromConfig($module, $data);
    }, $modules);
  }

  protected static function cleanModule($config) {
    unset($config['dataFilter']);
    unset($config['dataFilterArgs']);
    unset($config['customData']);

    if (empty($config['areas'])) {
      unset($config['areas']);
    }

    return $config;
  }
}
