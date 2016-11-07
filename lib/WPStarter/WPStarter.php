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

  public static function registerModule($moduleName) {
    // check if module already registered
    if(array_key_exists($moduleName, self::$modules)) {
      throw new Exception("Module {$moduleName} is already registered!");
    }

    // register module / require functions.php
    // TODO add get_template_directory() . '/Modules' as default path
    $modulePath = apply_filters('WPStarter/defaultModulesPath', '') . $moduleName;

    if(!is_dir($modulePath)) {
      throw new Exception("Register Module: Folder {$modulePath} not found!");
    }

    do_action('WPStarter/registerModule', $modulePath);
    do_action("WPStarter/registerModule?name={$moduleName}", $modulePath);

    $filePath = $modulePath . '/functions.php';
    if(file_exists($filePath)) {
      // require_once breaks the tests and is also unnecessary because of the validation above
      require $filePath;
    }

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
