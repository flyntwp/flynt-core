<?php
namespace Flynt;

use Flynt\Defaults;
use Flynt\BuildConstructionPlan;
use Flynt\Render;
use Flynt\ComponentManager;

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
  var_dump($cp);
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
function registerComponent($componentName, $componentPath = null) {
  $componentManager = ComponentManager::getInstance();
  $componentManager->registerComponent($componentName, $componentPath);
}

// @codingStandardsIgnoreLine
function registerComponents($components = []) {
  $componentManager = ComponentManager::getInstance();
  array_walk($components, function ($componentPath, $componentName) use ($componentManager) {
    if (is_int($componentName)) {
      $componentName = $componentPath;
      $componentPath = null;
    } else {
      $componentPath = (isset($componentPath)) ? $componentPath : null;
    }
    $componentManager->registerComponent($componentName, $componentPath);
  });
}
