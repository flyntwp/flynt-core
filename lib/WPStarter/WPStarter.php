<?php
namespace WPStarter;

use WPStarter\BuildConstructionPlan;
use WPStarter\Render;

class WPStarter {

  private static $_modules = [];

  public static function echoHtmlFromConfig($config) {
    echo self::getHtmlFromConfig($config);
  }

  public static function getHtmlFromConfig($config) {
    $cp = BuildConstructionPlan::fromConfig($config, self::$_modules);
    return Render::fromConstructionPlan($cp);
  }

  public static function echoHtmlFromConfigFile($fileName) {
    echo self::getHtmlFromConfigFile($fileName);
  }

  public static function getHtmlFromConfigFile($fileName) {
    $cp = BuildConstructionPlan::fromConfigFile($fileName, self::$_modules);
    return Render::fromConstructionPlan($cp);
  }

  public static function registerModule($moduleName, $modulePath = null) {
    // check if module already registered
    if (array_key_exists($moduleName, self::$_modules)) {
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

  public static function getModuleFilePath($moduleName, $fileName = 'index.php') {
    // check if module exists / is registered
    if (!array_key_exists($moduleName, self::$_modules)) {
      trigger_error("Cannot get module file: Module '{$moduleName}' is not registered!", E_USER_WARNING);
      return false;
    }

    // check if file exists (path in array already has a trailing slash)
    $filePath = self::$_modules[$moduleName] . $fileName;
    if (!is_file($filePath)) {
      trigger_error("Cannot get module file: File '{$fileName}' could not be found at '{$filePath}'!", E_USER_WARNING);
      return false;
    }

    return $filePath;
  }

  protected static function addModuleToList($name, $path) {
    self::$_modules[$name] = $path;
  }

  public static function getModuleList() {
    return self::$_modules;
  }
}
