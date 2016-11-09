<?php

namespace WPStarter;

use Exception;
use WPStarter\WPStarter;
use function WPStarter\Helpers\extractNestedDataFromArray;

class DefaultLoader {
  public static function init() {
    add_filter('WPStarter/configPath', ['WPStarter\DefaultLoader', 'addFilterConfigPath'], 999, 1);
    add_filter('WPStarter/configFileLoader', ['WPStarter\DefaultLoader', 'addFilterConfigFileLoader'], 999, 3);
    add_filter('WPStarter/renderModule', ['WPStarter\DefaultLoader', 'addFilterRenderModule'], 999, 3);
    add_filter('WPStarter/modulePath', ['WPStarter\DefaultLoader', 'addFilterModulePath'], 999, 2);
    add_action('WPStarter/renderModule', ['WPStarter\DefaultLoader', 'addActionRenderModule']);
  }

  public static function addFilterConfigPath($configPath) {
    if(is_null($configPath)) {
      $configPath = get_template_directory() . '/config';
    }
    return $configPath;
  }

  public static function addFilterConfigFileLoader($config, $configName, $configPath) {
    if(is_null($config)) {
      $config = json_decode(file_get_contents($configPath), true);
    }
    return $config;
  }

  public static function addFilterRenderModule($output, $moduleName, $moduleData, $areaHtml) {
    if(empty($output)) {
      $filePath = WPStarter::getModulePath($moduleName);
      $output = self::renderFile($moduleData, $areaHtml, $filePath);
    }
    return $output;
  }

  public static function addFilterModulePath($modulePath, $moduleName) {
    if(is_null($modulePath)) {
      $modulePath = get_template_directory() . '/Modules/' . $moduleName;
    }
    return $modulePath;
  }

  // this action needs to be removed by the user if they want to overwrite this functionality
  public static function addActionRenderModule($modulePath) {
    if(!is_dir($modulePath)) {
      trigger_error("Render Module: Folder {$modulePath} not found!", E_USER_WARNING);
    }
    $filePath = $modulePath . '/functions.php';
    if(file_exists($filePath)) {
      // require_once breaks the tests and is also unnecessary because of the validation above
      require $filePath;
    }
  }

  protected static function renderFile($moduleData, $areaHtml, $filePath) {
    if(!is_file($filePath)) {
      trigger_error("Template not found: {$filePath}", E_USER_WARNING);
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
