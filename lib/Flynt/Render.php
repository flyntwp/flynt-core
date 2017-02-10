<?php

namespace Flynt;

class Render {
  public static function fromConstructionPlan($constructionPlan) {
    $areaHtml = self::extractAreaHtml($constructionPlan);

    return self::applyRenderFilters($constructionPlan, $areaHtml);
  }

  protected static function validateConstructionPlan($constructionPlan) {
    if (empty($constructionPlan)) {
      trigger_error('Empty Construction Plan!', E_USER_WARNING);
      return false;
    } else {
      return true;
    }
  }

  protected static function extractAreaHtml($constructionPlan) {
    $areaHtml = [];
    if (array_key_exists('areas', $constructionPlan)) {
      $areaHtml = array_map('self::joinAreaComponents', $constructionPlan['areas']);
    }
    return $areaHtml;
  }

  protected static function joinAreaComponents($components) {
    return join('', array_map('self::fromConstructionPlan', $components));
  }

  protected static function applyRenderFilters($constructionPlan, $areaHtml) {
    if (self::validateConstructionPlan($constructionPlan) === false) {
      return '';
    }

    $componentData = $constructionPlan['data'];
    $componentName = $constructionPlan['name'];

    $output = apply_filters('Flynt/renderComponent', null, $componentName, $componentData, $areaHtml);
    $output = apply_filters(
      "Flynt/renderComponent?name={$componentName}",
      $output,
      $componentName,
      $componentData,
      $areaHtml
    );

    return is_null($output) ? '' : $output;
  }
}
