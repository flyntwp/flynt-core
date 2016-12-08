<?php
namespace Flynt;

class ModuleManager {

  protected $modules = [];
  protected static $instance = null;

  public static function getInstance() {
    if (null === self::$instance) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * clone
   *
   * Prevent cloning with 'protected' keyword
  **/
  protected function __clone() {
  }

  /**
   * constructor
   *
   * Prevent instantiation with 'protected' keyword
  **/
  protected function __construct() {
  }

  public function registerModule($moduleName, $modulePath = null) {
    // check if module already registered
    if (array_key_exists($moduleName, $this->modules)) {
      trigger_error("Module {$moduleName} is already registered!", E_USER_WARNING);
      return;
    }

    // register module / require functions.php
    $modulePath = trailingslashit(apply_filters('Flynt/modulePath', $modulePath, $moduleName));

    do_action('Flynt/registerModule', $modulePath, $moduleName);
    do_action("Flynt/registerModule?name={$moduleName}", $modulePath);

    // add module to internal list (array)
    return $this->add($moduleName, $modulePath);
  }

  public function getModuleFilePath($moduleName, $fileName = 'index.php') {
    // check if module exists / is registered
    if (!array_key_exists($moduleName, $this->modules)) {
      trigger_error("Cannot get module file: Module '{$moduleName}' is not registered!", E_USER_WARNING);
      return false;
    }

    // check if file exists (path in array already has a trailing slash)
    $filePath = $this->modules[$moduleName] . $fileName;
    if (!is_file($filePath)) {
      trigger_error("Cannot get module file: File '{$fileName}' could not be found at '{$filePath}'!", E_USER_WARNING);
      return false;
    }

    return $filePath;
  }

  protected function add($name, $path) {
    $this->modules[$name] = $path;
    return true;
  }

  public function getAll() {
    return $this->modules;
  }

  public function removeAll() {
    $this->modules = [];
  }
}
