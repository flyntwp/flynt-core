<?php
namespace Flynt;

class ComponentManager {

  protected $components = [];
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

  public function registerComponent($componentName, $componentPath = null) {
    // check if component already registered
    if (array_key_exists($componentName, $this->components)) {
      trigger_error("Component {$componentName} is already registered!", E_USER_WARNING);
      return;
    }

    // register component / require functions.php
    $componentPath = trailingslashit(apply_filters('Flynt/componentPath', $componentPath, $componentName));

    do_action('Flynt/registerComponent', $componentPath, $componentName);
    # TODO: out of consistency pass $componentName as well?
    do_action("Flynt/registerComponent?name={$componentName}", $componentPath);

    // add component to internal list (array)
    return $this->add($componentName, $componentPath);
  }

  public function getComponentFilePath($componentName, $fileName = 'index.php') {
    // check if component exists / is registered
    if (!array_key_exists($componentName, $this->components)) {
      trigger_error("Cannot get component file: Component '{$componentName}' is not registered!", E_USER_WARNING);
      return false;
    }

    // check if file exists (path in array already has a trailing slash)
    $filePath = $this->components[$componentName] . $fileName;
    if (!is_file($filePath)) {
      trigger_error(
        "Cannot get component file: File '{$fileName}' could not be found at '{$filePath}'!",
        E_USER_WARNING
      );
      return false;
    }

    return $filePath;
  }

  protected function add($name, $path) {
    $this->components[$name] = $path;
    return true;
  }

  public function getAll() {
    return $this->components;
  }

  public function removeAll() {
    $this->components = [];
  }

  public function isRegistered($componentName) {
    return array_key_exists($componentName, $this->components);
  }
}
