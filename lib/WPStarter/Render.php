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
      $areaHtml = array_map('self::joinAreaModules', $constructionPlan['areas']);
    }
    $moduleData = $constructionPlan['data'];
    $moduleName = $constructionPlan['name'];

    $output = apply_filters('WPStarter/renderModule', '', $moduleName, $moduleData, $areaHtml);
    return apply_filters("WPStarter/renderModule?name={$moduleName}", $output, $moduleName, $moduleData, $areaHtml);
  }

  protected static function joinAreaModules($modules) {
    return join('', array_map('self::fromConstructionPlan', $modules));
  }
}
