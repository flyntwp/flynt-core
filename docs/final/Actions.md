# Actions

## Flynt/registerModule
Exectuted when any or a specific module is registered. Can be used to load additional files.

Module specific action: `Flynt/registerModule?name={$config['name']}`

Arguments for callable:
<dl>
  <dt>$modulePath</dt>
  <dd>the path of the module</dd>

  <dt>$moduleName*</dt>
  <dd>the name of the module (*not available in module specific action)</dd>
</dl>

Defaults:
```php
add_action('Flynt/registerModule', ['Flynt\Defaults', 'checkModuleFolder']);
add_action('Flynt/registerModule', ['Flynt\Defaults', 'loadFunctionsFile']);
```
```php
namespace Flynt;
class Defaults {
  public static function checkModuleFolder($modulePath) {
    if (!is_dir($modulePath)) {
      trigger_error("Register Module: Folder {$modulePath} not found!", E_USER_WARNING);
    }
  }
  public static function loadFunctionsFile($modulePath) {
    $filePath = $modulePath . '/functions.php';
    if (file_exists($filePath)) {
      require_once $filePath;
    }
  }
}
```
