<?php
namespace WPStarter;

use WPStarter\BuildConstructionPlan;
use WPStarter\Render;
use WPStarter\ModuleManager;

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
