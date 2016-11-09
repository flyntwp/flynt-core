# WP Starter Construction Plan Class

The Construction Plan is ...

## Public API
- Loading from a Configuration Array
- Loading from a Configuration File

## Filters and Actions

## Configuration Structure
- Name
- DataFilter
- CustomData
- Areas

## test
```php
<?php

add_filter('WPStarter/renderModule', function($moduleName, $data) {
  return Timber::render($moduleName, $data);
});
```


is this what we want??

```php
<?php

if (is_wp_error(WPStarter::echoHtmlFromConfigFile('exampleConfig.cson'))) {
  the_loop();
}

```


## Example Usage of the dynamicSubmodules filter

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
})
```

or in a config:

```coffeescript
name: 'Page'
dataFilter: 'currentPage'
areas:
  mainContent: [{
    name: 'MainContent'
    areas:
      pageModules: [
        {
          name: 'FlexibleContent'
          dataFilter: 'FlexibleContent'
          dataFilterArgs:
            'pageModules'
          # customData:
          #   fieldGroup: 'pageModules'
        }
      ]
  }]
```

### Notes on Basic Usage of the Starter
```php
<?php
// register module in the functions.php
WPStarter::registerModule('SingleModule');

// render starter with wrapper class
WPStarter::echoFromConfig([
  'name' => 'SingleModule',
  'dataFilter' => 'WPStarter/DataFilters/SingleModule/foo'
])
```

### Adding styles and scripts
```php
<?php
// add something like these by default
add_action('WPStarter/registerModule', function () {});
add_action('WPStarter/registerModule/registerStyle', function($path) {})
add_action('WPStarter/registerModule/registerScript', function($path) {});

// remove in theme if necessary
remove_action('WPStarter/registerModule/registerStyle', ['WPStarter', 'registerStyle']);
```
