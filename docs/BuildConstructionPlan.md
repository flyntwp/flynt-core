# `BuildConstructionPlan` Class

The Construction Plan is ...

## Table of Contents
1. [Building a Construction Plan](#1.-building-a-construction-plan)
2. [Using Data Filters](#2.-using-data-filters)
3. [Adding Submodules Dynamically](#3.-adding-submodules-dynamically)
4. [References](#4.-references)

## 1. Building a Construction Plan

## 2. Using Data Filters

## 3. Adding Submodules Dynamically

### Example Usage of the dynamicSubmodules filter

```php
<?php
add_filter("WPStarter/dynamicSubmodules?name=ParentModule", function($areas, $data, $parentData) {
  $areas['area51'] = [
    'name' => 'ChildModuleName',
    'dataFilter' => 'WPStarter/DataFilters/ChildModuleName/foo',
    'customData' => [
      'test1' => 1
    ]
  ]
  return $areas;
}, 10, 3);
```

## 4. References

### Public Static Class Methods
#### fromConfig
Loading from a Configuration Array.

#### fromConfigFile
Loading from a Configuration File.

### Filters
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

#### `WPStarter/dynamicSubmodules?name={ModuleNameInConfig}`
#### Custom Data Filter

### Actions
This class has no actions.

### Configuration Structure
- Name
- DataFilter
- CustomData
- Areas

### Example config.json

```json
"name": "Page",
"dataFilter": "currentPage",
"areas": {
  "mainContent": [
    {
      "name": "MainContent",
      "areas": {
        "pageModules": [
          {
            "name": "FlexibleContent",
            "dataFilter": "FlexibleContent",
            "dataFilterArgs": [
              "pageModules"
            ]
          }
        ]
      }
    }
  ]
}
```
