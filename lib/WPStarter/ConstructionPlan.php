<?php

namespace WPStarter;

class ConstructionPlan {
  private static $moduleList = [];

  public static function fromConfig($config, $moduleList) {
    self::$moduleList = $moduleList;
    return self::fromConfigRecursive($config);
  }

  protected static function fromConfigRecursive($config, $parentData = []) {

    // Check configuration for errors
    if (false === self::validateConfig($config)) {
      return [];
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

  public static function fromConfigFile($configFileName, $moduleList) {
    $configPath = trailingslashit(apply_filters('WPStarter/configPath', null, $configFileName));
    $configFilePath = $configPath . $configFileName;
    if (!is_file($configFilePath)) {
      trigger_error('Config file not found: ' . $configFilePath, E_USER_WARNING);
      return [];
    }
    $config = apply_filters('WPStarter/configFileLoader', null, $configFileName, $configFilePath);
    return self::fromConfig($config, $moduleList);
  }

  protected static function validateConfig($config) {
    if(!is_array($config)) {
      trigger_error('Config needs to be an array! ' . gettype($config) . ' given.', E_USER_WARNING);
      return false;
    }
    if(empty($config)) {
      trigger_error('Config is empty!', E_USER_WARNING);
      return false;
    }
    if(!array_key_exists('name', $config)) {
      trigger_error('No module name given! Please make sure every module has at least a \'name\' attribute.', E_USER_WARNING);
      return false;
    }
    // check if this module is registered
    if(!array_key_exists($config['name'], self::$moduleList)) {
      trigger_error("Module '{$config['name']}' could not be found in module list. Did you forget to register the module?", E_USER_WARNING);
      return false;
    }
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
      return self::fromConfigRecursive($module, $data);
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
