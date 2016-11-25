### Dataflow

When **echoHtmlFromConfig** is called in a template file it will result in the following dataflow. You have the configuration and WordPress hooks to modify the outcome.

1. echoHtmlFromConfig

```php
echoHtmlFromConfig([
  'name' => 'ExampleModule',
  'dataFilter' => 'Flynt/DataFilters/ExampleFilter'
  'dataFilterArgs' => ['foo'],
  'customData' => [
    'bar' => 'baz'
  ],
  'areas' => [
    'childrenTop' => [
      [
        'name' => 'ChildModule1'
      ]
    ],
    'childrenBottom' => [
      [
        'name' => 'ChildModule2',
        'customData' => [
          'custom' => 'data'
        ]
      ], [
        'name' => 'ChildModule3',
        'parentData' => [
          'parent' => 'data'
        ],
        'customData' => [
          'custom' => 'data'
        ]
      ]
    ]
  ]
]);
```
2. To determine the module data the following functions will be executed (simplified):

```php
// ExampleModule
$moduleData = [];
$moduleData = apply_filters_ref_array('Flynt/DataFilters/ExampleFilter', [$moduleData, 'foo']);
$moduleData = array_merge($moduleData, ['bar' => 'baz']);
$moduleData = apply_filters('WPStarter/modifyModuleData', $moduleData, ...);
$moduleData = apply_filters('WPStarter/modifyModuleData?name=ExampleModule', $moduleData, ...);
```

  The same will be done for all the modules in the ExampleModule's areas. In particular:

```php
// ChildModule1
$moduleData = $parentModuleData;
```
```php
// ChildModule2
$moduleData = $parentModuleData;
$moduleData = ['custom' => 'data'];
```
```php
// ChildModule3
$parentModuleData = ['parent' => 'data'];
$moduleData = $parentModuleData;
$moduleData = ['custom' => 'data'];
```

3.
