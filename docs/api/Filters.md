# WordPress Filters

## Flynt/modulePath
Modify the path of a module.

Arguments for callable:
<dl>
  <dt>$modulePath</dt>
  <dd>the path of the module</dd>

  <dt>$moduleName</dt>
  <dd>the name of the module</dd>
</dl>

Default:
```php
add_filter('Flynt/modulePath', ['Flynt\Defaults', 'setModulePath'], 999, 2);
```
```php
namespace Flynt;
class Defaults {
  public static function setModulePath($modulePath, $moduleName) {
    if (is_null($modulePath)) {
      $modulePath = self::getModulesDirectory() . '/' . $moduleName;
    }
    return $modulePath;
  }
}
```
## Flynt/configPath
Modify the path of the config files that can be specified in `(echo|get)HtmlFromConfigFile`

Arguments for callable:
<dl>
  <dt>$configPath</dt>
  <dd>the path of the config file</dd>

  <dt>$configFileName</dt>
  <dd>the name of the config file</dd>
</dl>

Default:
```php
add_filter('Flynt/configPath', ['Flynt\Defaults', 'setConfigPath'], 999, 2);
```
```php
namespace Flynt;
class Defaults {
  public static function setConfigPath($configPath, $configFileName) {
    if (is_null($configPath)) {
      $configPath = get_template_directory() . '/' . self::CONFIG_DIR . '/' . $configFileName;
    }
    return $configPath;
  }
}
```
## Flynt/configFileLoader
Modify the logic of loading a config file. Default loads `json` files. Can be used to load other formats like `yaml`.

Arguments for callable:
<dl>
  <dt>$config</dt>
  <dd>the loaded config. `null` by default</dd>

  <dt>$configName</dt>
  <dd>the path of the config file</dd>

  <dt>$configPath</dt>
  <dd>the name of the config file</dd>
</dl>

Default:
```php
add_filter('Flynt/configFileLoader', ['Flynt\Defaults', 'loadConfigFile'], 999, 3);
```
```php
namespace Flynt;
class Defaults {
  public static function loadConfigFile($config, $configName, $configPath) {
    if (is_null($config)) {
      $config = json_decode(file_get_contents($configPath), true);
    }
    return $config;
  }
}
```
## Flynt/initModuleConfig
Modify the config unsed to build the construction plan.

Module specific filter: `Flynt/initModuleConfig?name={$config['name']}`
## Flynt/modifyModuleData
Final point to modify the data of a module. Called after the data filters and adding custom data. This is the default place to do data manipulation and preparation before passing it to the render function.

Module specific filter: `Flynt/modifyModuleData?name={$config['name']}`

Arguments for callable:
<dl>
  <dt>$data</dt>
  <dd>the module's data that will be used for rendering</dd>

  <dt>$parentData</dt>
  <dd>the module's parent's data</dd>

  <dt>$config</dt>
  <dd>entire config of the module</dd>
</dl>

Example:
```php
add_filter('WPStarter/modifyModuleData?name=PageHeader', function ($data, $parentData) {
  if (!empty($parentData['post_thumbnail']) && array_key_exists('url', $parentData['post_thumbnail'])) {
    $data['image'] = $parentData['post_thumbnail']['url'];
  }
  return $data;
}, 10, 2);
```
## Flynt/dynamicSubmodules?name={$config['name']}
Modify the module's areas. Can be used to dynamically add sub modules based on data comming from the data filter.

Arguments for callable:
<dl>
  <dt>$areas</dt>
  <dd>the rendered HTML</dd>

  <dt>$moduleData</dt>
  <dd>the module's data</dd>

  <dt>$parentData</dt>
  <dd>the module's parent's data</dd>
</dl>

Example:

```php
add_filter('Flynt/dynamicSubmodules?name=FlexibleContent', function ($areas, $data, $parentData) {
  $fieldGroup = $data['fieldGroup'];
  if (array_key_exists($fieldGroup, $parentData) && $parentData[$fieldGroup] !== false) {
    $areas['flexibleContent'] = array_map(function ($field) use ($parentData) {
      return [
        'name' => ucfirst($field['acf_fc_layout']),
        'customData' => $field,
        'parentData' => $parentData // overwrite parent data of child modules
      ];
    }, $parentData[$data['fieldGroup']]);
  }
  return $areas;
}, 10, 3);
```

## Flynt/renderModule
Specify the way how modules or a single module should be rendered.

Module specific filter: `Flynt/renderModule?name={$moduleName}`

Arguments for callable:
<dl>
  <dt>$output</dt>
  <dd>the rendered HTML</dd>

  <dt>$moduleName</dt>
  <dd>the name of the module</dd>

  <dt>$moduleData</dt>
  <dd>the module's data</dd>

  <dt>$areaHtml</dt>
  <dd>the rendered HTML of the module's areas</dd>
</dl>

Default:
```php
add_filter('Flynt/renderModule', ['Flynt\Defaults', 'renderModule'], 999, 4);
```
```php
namespace Flynt;
class Defaults {
  public static function renderModule($output, $moduleName, $moduleData, $areaHtml) {
    if (is_null($output)) {
      $moduleManager = ModuleManager::getInstance();
      $filePath = $moduleManager->getModuleFilePath($moduleName);
      $output = self::renderFile($moduleData, $areaHtml, $filePath);
    }
    return $output;
  }
}
```
