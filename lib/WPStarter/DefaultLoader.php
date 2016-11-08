<?php

namespace WPStarter;

use Exception;
use WPStarter\WPStarter;
use WPStarter\Helpers\extractNestedDataFromArray;

class DefaultLoader {
  public static function init() {
    add_filter('WPStarter/configPath', ['WPStarter\DefaultLoader', 'addFilterConfigPath'], 999, 1);
    add_filter('WPStarter/configFileLoader', ['WPStarter\DefaultLoader', 'addFilterConfigFileLoader'], 999, 3);
    add_filter('WPStarter/renderModule', ['WPStarter\DefaultLoader', 'addFilterRenderModule'], 999, 3);
  }

  public static function addFilterConfigPath($configPath) {
    if(is_null($configPath)) {
      $configPath = get_template_directory() . '/config';
    }
    return $configPath;
  }

  public static function addFilterConfigFileLoader($config, $configName, $configPath) {
    if (is_null($config)) {
      $config = json_decode(file_get_contents($configPath), true);
    }
    return $config;
  }

  public static function addFilterRenderModule($output, $moduleName, $moduleData, $areaHtml) {
    if (empty($output)) {
      $filePath = WPStarter::getModulePath($moduleName);
      $output = self::renderFile($moduleData, $areaHtml, $filePath);
    }
    return $output;
  }

  protected static function renderFile($moduleData, $areaHtml, $filePath) {
    if(!is_file($filePath)) {
      // TODO should be a warning
      throw new Exception("Template not found: {$filePath}");
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
