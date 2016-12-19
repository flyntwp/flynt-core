<?php

namespace Flynt;

class BuildConstructionPlan {
  public static function fromConfig($config) {
    return self::fromConfigRecursive($config);
  }

  protected static function fromConfigRecursive($config, $areaName = null, $parentData = []) {
    // Check configuration for errors
    if (false === self::validateConfig($config)) {
      return [];
    }

    $config['data'] = [];

    // check for parent data overwrite
    $parentData = self::overwriteParentData($config, $parentData);

    // applies filters for component initialisation
    # TODO: rename function and filters
    $config = self::initComponentConfig($config, $areaName, $parentData);

    // add data to component
    $config = self::applyDataFilter($config);
    $config = self::addCustomData($config);

    // apply modifyComponentData filters to be used in a functions.php of a component for example
    $config = self::applyDataModifications($config, $parentData);

    // add subcomponents (dynamic + static) and return construction plan for the current component
    return self::addSubcomponents($config, $parentData);
  }

  public static function fromConfigFile($configFileName) {
    $configFilePath = apply_filters('Flynt/configPath', null, $configFileName);
    if (!is_file($configFilePath)) {
      trigger_error('Config file not found: ' . $configFilePath, E_USER_WARNING);
      return [];
    }
    $config = apply_filters('Flynt/configFileLoader', null, $configFileName, $configFilePath);
    return self::fromConfig($config);
  }

  protected static function validateConfig($config) {
    if (!is_array($config)) {
      trigger_error('Config needs to be an array! ' . gettype($config) . ' given.', E_USER_WARNING);
      return false;
    }
    if (empty($config)) {
      trigger_error('Config is empty!', E_USER_WARNING);
      return false;
    }
    if (!array_key_exists('name', $config)) {
      trigger_error(
        'No component name given! Please make sure every component has at least a \'name\' attribute.',
        E_USER_WARNING
      );
      return false;
    }
    // check if this component is registered
    $componentManager = ComponentManager::getInstance();
    # TODO: use ModuleManager#isRegistered, not array_key_exists
    if (!array_key_exists($config['name'], $componentManager->getAll())) {
      trigger_error(
        "Component '{$config['name']}' could not be found in component list. Did you forget to register the component?",
        E_USER_WARNING
      );
      return false;
    }
  }

  protected static function overwriteParentData(&$config, $parentData) {
    if (array_key_exists('parentData', $config)) {
      $parentData = $config['parentData'];
      unset($config['parentData']);
    }
    return $parentData;
  }

  protected static function initComponentConfig($config, $areaName, $parentData) {
    $config = apply_filters(
      'Flynt/initComponentConfig',
      $config,
      $areaName,
      $parentData
    );
    return apply_filters(
      "Flynt/initComponentConfig?name={$config['name']}",
      $config,
      $areaName,
      $parentData
    );
  }

  protected static function applyDataFilter($config) {
    if (array_key_exists('dataFilter', $config)) {
      $args = [ $config['data'] ];
      if (array_key_exists('dataFilterArgs', $config)) {
        $args = array_merge($args, $config['dataFilterArgs']);
        unset($config['dataFilterArgs']);
      }
      $config['data'] = apply_filters_ref_array($config['dataFilter'], $args);
      unset($config['dataFilter']);
    }
    return $config;
  }

  protected static function addCustomData($config) {
    if (array_key_exists('customData', $config)) {
      // custom data overwrites original data
      $config['data'] = array_merge($config['data'], $config['customData']);
      unset($config['customData']);
    }
    return $config;
  }

  protected static function applyDataModifications($config, $parentData) {
    $config['data'] = apply_filters(
      'Flynt/modifyComponentData',
      $config['data'],
      $parentData,
      $config
    );
    $config['data'] = apply_filters(
      "Flynt/modifyComponentData?name={$config['name']}",
      $config['data'],
      $parentData,
      $config
    );
    return $config;
  }

  protected static function addSubcomponents($config, $parentData) {
    // add dynamic subcomponents to areas
    $areas = array_key_exists('areas', $config) ? $config['areas'] : [];
    $config['areas'] = apply_filters(
      "Flynt/dynamicSubcomponents?name={$config['name']}",
      $areas,
      $config['data'],
      $parentData
    );

    // iterate areas and recursively map child component construction plan
    if (!empty($config['areas'])) {
      $areaNames = array_keys($config['areas']);
      $config['areas'] = array_reduce($areaNames, function ($output, $areaName) use ($config, $parentData) {
        $components = $config['areas'][$areaName];
        $output[$areaName] = self::mapAreaComponents($components, $config, $areaName, $parentData);
        return $output;
      }, []);
    }

    // remove empty 'areas' key from config
    // this can happen if:
    // 1. there were no areas defined to begin with
    // 2. there were areas defined, but no components in them
    if (empty($config['areas'])) {
      unset($config['areas']);
    }

    return $config;
  }

  protected static function mapAreaComponents($components, $config, $areaName, $parentData) {
    return array_map(function ($component) use ($config, $areaName, $parentData) {
      $data = empty($config['data']) ? $parentData : $config['data'];
      return self::fromConfigRecursive($component, $areaName, $data);
    }, $components);
  }
}
