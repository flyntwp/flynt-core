<?php

namespace WPStarter;

use function WPStarter\Helpers\extractNestedDataFromArray;

class Render {
  public static function fromConstructionPlan($constructionPlan) {
    self::validateConstructionPlan($constructionPlan);

    $areaHtml = self::extractAreaHtml($constructionPlan);

    return self::applyRenderFilters($constructionPlan, $areaHtml);
  }

  protected static function validateConstructionPlan($constructionPlan) {
    if (empty($constructionPlan)) {
      trigger_error('Empty Construction Plan!', E_USER_WARNING);
    }
  }

  protected static function extractAreaHtml($constructionPlan) {
    $areaHtml = [];
    if(array_key_exists('areas', $constructionPlan)) {
      $areaHtml = array_map('self::joinAreaModules', $constructionPlan['areas']);
    }
    return $areaHtml;
  }

  protected static function joinAreaModules($modules) {
    return join('', array_map('self::fromConstructionPlan', $modules));
  }

  protected static function applyRenderFilters($constructionPlan, $areaHtml) {
    $moduleData = $constructionPlan['data'];
    $moduleName = $constructionPlan['name'];

    $output = apply_filters('WPStarter/renderModule', '', $moduleName, $moduleData, $areaHtml);
    return apply_filters("WPStarter/renderModule?name={$moduleName}", $output, $moduleName, $moduleData, $areaHtml);
  }
}
