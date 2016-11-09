<?php
namespace WPStarter;

use Exception;
use WPStarter\ConstructionPlan;
use WPStarter\Render;

class WPStarter {

  private static $modules = [];

  public static function echoHtmlFromConfig($config) {
    echo self::getHtmlFromConfig($config);
  }

  public static function getHtmlFromConfig($config) {
    $cp = ConstructionPlan::fromConfig($config, self::$modules);
    return Render::fromConstructionPlan($cp);
  }

  public static function echoHtmlFromConfigFile($fileName) {
    echo self::getHtmlFromConfigFile($fileName);
  }

  public static function getHtmlFromConfigFile($fileName) {
    $cp = ConstructionPlan::fromConfigFile($fileName, self::$modules);
    return Render::fromConstructionPlan($cp);
  }

  public static function registerModule($moduleName, $modulePath = null) {
    // check if module already registered
    if(array_key_exists($moduleName, self::$modules)) {
      trigger_error("Module {$moduleName} is already registered!", E_USER_WARNING);
      return;
    }

    // register module / require functions.php
    $modulePath = trailingslashit(apply_filters('WPStarter/modulePath', $modulePath, $moduleName));

    do_action('WPStarter/registerModule', $modulePath, $moduleName);
    do_action("WPStarter/registerModule?name={$moduleName}", $modulePath);

    // add module to internal list (array)
    self::addModuleToList($moduleName, $modulePath);
  }

  protected static function addModuleToList($name, $path) {
    self::$modules[$name] = $path;
  }

  public static function getModuleList() {
    return self::$modules;
  }
}
