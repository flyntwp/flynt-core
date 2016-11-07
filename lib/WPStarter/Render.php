<?php

namespace WPStarter;

use Exception;
use function WPStarter\Helpers\extractNestedDataFromArray;

class Render {
  public static function fromConstructionPlan($constructionPlan) {
    if (empty($constructionPlan)) {
      throw new Exception('Empty Construction Plan!');
    }
    $areaHtml = [];
    if(array_key_exists('areas', $constructionPlan)) {
      $areaHtml = array_map(function($areaModules) {
        return self::joinAreaModules($areaModules);
      }, $constructionPlan['areas']);
    }
    $data = $constructionPlan['data'];
    $moduleName = $constructionPlan['name'];

    $output = apply_filters('WPStarter/Render/renderModule', '', $data);
    $output = apply_filters("WPStarter/Render/renderModule?name={$moduleName}", $output, $data);

    if (empty($output)) {
      $filePath = apply_filters('WPStarter/defaultModulesPath', '') . "{$moduleName}/index.php";
      return self::renderFile($data, $areaHtml, $filePath);
    }
    return $output;
  }

  protected static function renderFile($moduleData, $areaHtml, $filePath) {
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

    if(!file_exists($filePath)) {
      throw new Exception("Template not found: {$filePath}");
    }

    ob_start();
    require $filePath;
    $output = ob_get_contents();
    ob_get_clean();

    return $output;
  }

  protected static function joinAreaModules($modules) {
    return join('', array_map(function($module) {
      return self::fromConstructionPlan($module);
    }, $modules));
  }
}
