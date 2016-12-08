<?php
namespace Flynt;

use Flynt\Defaults;
use Flynt\BuildConstructionPlan;
use Flynt\Render;
use Flynt\ModuleManager;

// @codingStandardsIgnoreLine
function initDefaults() {
  Defaults::init();
}

// @codingStandardsIgnoreLine
function echoHtmlFromConfig($config) {
  echo getHtmlFromConfig($config);
}

// @codingStandardsIgnoreLine
function getHtmlFromConfig($config) {
  $cp = BuildConstructionPlan::fromConfig($config);
  return Render::fromConstructionPlan($cp);
}

// @codingStandardsIgnoreLine
function echoHtmlFromConfigFile($fileName) {
  echo getHtmlFromConfigFile($fileName);
}

// @codingStandardsIgnoreLine
function getHtmlFromConfigFile($fileName) {
  $cp = BuildConstructionPlan::fromConfigFile($fileName);
  return Render::fromConstructionPlan($cp);
}

// @codingStandardsIgnoreLine
function registerModule($moduleName, $modulePath = null) {
  $moduleManager = ModuleManager::getInstance();
  $moduleManager->registerModule($moduleName, $modulePath);
}

// @codingStandardsIgnoreLine
function registerModules($modules = []) {
  $moduleManager = ModuleManager::getInstance();
  foreach ($modules as $moduleName => $modulePath) {
    if (is_int($moduleName)) {
      $moduleName = $modulePath;
      $modulePath = null;
    } else {
      $modulePath = (isset($modulePath)) ? $modulePath : null;
    }
    $moduleManager->registerModule($moduleName, $modulePath);
  }
}
