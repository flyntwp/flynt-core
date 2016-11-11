<?php

namespace WPStarter;

use WPStarter\WPStarter;
use function WPStarter\Helpers\extractNestedDataFromArray;

class DefaultLoader {
  public static function init() {
    add_filter('WPStarter/configPath', ['WPStarter\DefaultLoader', 'setConfigPath'], 999, 1);
    add_filter('WPStarter/configFileLoader', ['WPStarter\DefaultLoader', 'loadConfigFile'], 999, 3);
    add_filter('WPStarter/renderModule', ['WPStarter\DefaultLoader', 'renderModule'], 999, 3);
    add_filter('WPStarter/modulePath', ['WPStarter\DefaultLoader', 'setModulePath'], 999, 2);
    add_action('WPStarter/registerModule', ['WPStarter\DefaultLoader', 'checkModuleFolder']);
    add_action('WPStarter/registerModule', ['WPStarter\DefaultLoader', 'loadFunctionsFile']);
  }

  public static function setConfigPath($configPath) {
    if(is_null($configPath)) {
      $configPath = get_template_directory() . '/config';
    }
    return $configPath;
  }

  public static function loadConfigFile($config, $configName, $configPath) {
    if(is_null($config)) {
      $config = json_decode(file_get_contents($configPath), true);
    }
    return $config;
  }

  public static function renderModule($output, $moduleName, $moduleData, $areaHtml) {
    if(empty($output)) {
      $filePath = WPStarter::getModuleFilePath($moduleName);
      $output = self::renderFile($moduleData, $areaHtml, $filePath);
    }
    return $output;
  }

  public static function setModulePath($modulePath, $moduleName) {
    if(is_null($modulePath)) {
      $modulePath = get_template_directory() . '/Modules/' . $moduleName;
    }
    return $modulePath;
  }

  // this action needs to be removed by the user if they want to overwrite this functionality
  public static function checkModuleFolder($modulePath) {
    if(!is_dir($modulePath)) {
      trigger_error("Register Module: Folder {$modulePath} not found!", E_USER_WARNING);
    }
  }

  // this action needs to be removed by the user if they want to overwrite this functionality
  public static function loadFunctionsFile($modulePath) {
    $filePath = $modulePath . '/functions.php';
    if(file_exists($filePath)) {
      require_once $filePath;
    }
  }

  protected static function renderFile($moduleData, $areaHtml, $filePath) {
    if(!is_file($filePath)) {
      trigger_error("Template not found: {$filePath}", E_USER_WARNING);
      return '';
    }

    $area = function($areaName) use ($areaHtml){
      if (array_key_exists($areaName, $areaHtml)) {
        return $areaHtml[$areaName];
      }
    };

    $data = function() use ($moduleData){
      $args = func_get_args();
      array_unshift($args, $moduleData);
      return extractNestedDataFromArray($args);
    };

    ob_start();
    require $filePath;
    $output = ob_get_contents();
    ob_get_clean();

    return $output;
  }
}
