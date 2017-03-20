<?php
namespace Flynt;

use Flynt\Defaults;
use Flynt\BuildConstructionPlan;
use Flynt\Render;
use Flynt\ComponentManager;

function initDefaults()
{
    Defaults::init();
}

function echoHtmlFromConfig($config)
{
    echo getHtmlFromConfig($config);
}

function getHtmlFromConfig($config)
{
    $cp = BuildConstructionPlan::fromConfig($config);
    return Render::fromConstructionPlan($cp);
}

function echoHtmlFromConfigFile($fileName)
{
    echo getHtmlFromConfigFile($fileName);
}

function getHtmlFromConfigFile($fileName)
{
    $cp = BuildConstructionPlan::fromConfigFile($fileName);
    return Render::fromConstructionPlan($cp);
}

function registerComponent($componentName, $componentPath = null)
{
    $componentManager = ComponentManager::getInstance();
    $componentManager->registerComponent($componentName, $componentPath);
}

function registerComponents($components = [])
{
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
