## 3. Adding Submodules Dynamically

### Example Usage of the dynamicSubmodules filter

```php
<?php
add_filter("WPStarter/dynamicSubmodules?name=ParentModule", function($areas, $data, $parentData) {
  $areas['area51'] = [
    [
      'name' => 'ChildModuleName',
      'dataFilter' => 'WPStarter/DataFilters/ChildModuleName/foo',
      'customData' => [
        'test1' => 1
      ]
    ]
  ];
  return $areas;
}, 10, 3);
```

#### `WPStarter/configPath`
Use this filter to set the path to your desired config file. Accepts up to two arguments: `$filePath` and `$fileName` including ending. Defaults to `{theme-folder}/config/{$fileName}`

Example:
```php
<?php
add_filter('WPStarter/configPath', function($filePath, $fileName) {
  return get_template_directory() . '/someConfigFolder/' . $fileName;
}, 10, 2);
```

The original filter is overwritten as long as the filter priority is < 999.

#### `WPStarter/configFileLoader`
Use this filter to define your own custom config loading mechanism. Accepts up to three arguments: `$configArray`, `$configFileName`, `$configFilePath`. By default it runs a `json_decode` on the expected json file and returns the resulting array.

Example for loading `.yaml` files instead:
```php
<?php
add_filter('WPStarter/configFileLoader', function ($configArray, $configFileName, $configFilePath) {
  return yaml_parse_file($configFilePath);
}, 10, 3);
```

The original filter is overwritten as long as the filter priority is < 999.

## 3. Getting Started

Assuming you have a folder called **Modules** in your theme directory, which looks something like this:
```
- my-theme
| - Modules
  | - SimpleModule
    | - index.php
| - style.css
| - functions.php
| - index.php
```

Using the plugin is as simple as putting the following code into your functions.php and index.php:

- TODO _Check `use WPStarter` statement_

_my-theme/functions.php_
```php
<?php
// register your module
WPStarter\registerModule('SimpleModule');
```

_my-theme/index.php_
```php
<?php
// render the index.php of your simple module
WPStarter\echoHtmlFromConfig([
  'name' => 'SimpleModule'
]);
```

_my-theme/Modules/SimpleModule/index.php_
```php
<?php
echo 'Hello World!';
```

TODO _Add example image of rendered version_

### Changing the default module directory

Add this to your functions.php and customize it according to your needs:
```php
<?php
add_filter('WPStarter/modulePath', function($modulePath, $moduleName) {
  $modulePath = get_template_directory() . '/{YourFolderInYourTheme}/' . $moduleName;
  return $modulePath;
}, 10, 2);
```

### Loading additional files in a module
```php
<?php
add_action('WPStarter/registerModule', function($modulePath) {
  $filePath = $modulePath . '/some-file.php';
  if(file_exists($filePath)) {
    require_once $filePath;
  }
});
```

### If you don't want to load a functions.php
```php
<?php
remove_action('WPStarter/registerModule', ['WPStarter\DefaultLoader', 'loadFunctionsPhp']);
```

### Adding Data to a Module

- TODO _Add overwriting exaplanation with customData_

To add data to a module, first register a Datafilter in your configuration array / file.
```php
<?php
WPStarter\echoHtmlFromConfig([
  'name' => 'SimpleModule',
  'dataFilter' => 'MyFilters/myFilterName'
])
```

Then use a filter with the specified name that returns the expected data:
```php
<?php
add_filter('MyFilters/myFilterName', function($data) {
  return [
    'foo' => 'bar',
    'baz' => 0
  ];
});
```

### Using a different renderer

- TODO _Check if this works_
- TODO _Add link to Timber docs_

_How to include Timber in your project is not covered here. Please check the Timber Documentation for further info_

Add this filter to your functions.php

```php
<?php
add_filter('WPStarter/renderModule', function($output, $moduleName, $moduleData, $areaHtml) {
  // get index file
  $moduleManager = WPStarter\ModuleManager::getInstance();
  $filePath = $moduleManager->getModuleFilePath($moduleName, 'index.twig');

  // Add areas to data
  $data = array_merge($moduleData, ['areas' => $areaHtml]);

  // return html rendered by timber / twig
  return Timber::fetch($filePath, $data);
}, 10, 4);
```


# jade content bla

```jade
//- TODO this doesn't work yet (object)
= $data('someObject', 'first')
//- this works
= $data('foo')('shmaz')
//- this works
- var_dump($data('someObject')->first . $data('bodyClass'))
```
