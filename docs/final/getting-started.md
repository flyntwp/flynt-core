# Getting Started

To get started with using the Flynt Core plugin just install it into the standard WordPress folder, activate it via the admin area and you are good to go.

Keep in mind that you want to consider using the Flynt theme in order to get all the benefits of the Flynt framework.

## First steps

The most basic way of using Flynt Core is this *Hello, world* example:

In your theme's `index.php` add
```php
Flynt\echoHtmlFromConfig([
  'name' => 'HelloWorld'
]);
```
and to your theme's `functions.php`
```php
add_filter('Flynt\renderModule?name=HelloWorld', function () {
  return 'Hello, world!';
});
```
That's it. You have used Flynt Core for the first time.

## Next steps

In order to use the intended structure for modules, load additional module scripts, and enable PHP file rendering initialize the plugin's defaults:
```php
Flynt\initDefaults();
```
This will add the following hooks:
```php
add_filter('Flynt/configPath', ['Flynt\Defaults', 'setConfigPath'], 999, 2);
add_filter('Flynt/configFileLoader', ['Flynt\Defaults', 'loadConfigFile'], 999, 3);
add_filter('Flynt/modulePath', ['Flynt\Defaults', 'setModulePath'], 999, 2);
add_action('Flynt/registerModule', ['Flynt\Defaults', 'checkModuleFolder']);
add_action('Flynt/registerModule', ['Flynt\Defaults', 'loadFunctionsFile']);
add_filter('Flynt/renderModule', ['Flynt\Defaults', 'renderModule'], 999, 4);
```
It will
- load config files from `./config`
- parse `.json` config files
- set the module path to `./Modules`
- require modules to be registered
- load `./Modules/{$moduleName}/function.php` from every registered module, if the file exists
- render `./Modules/{$moduleName}/index.php` and make view helper function `$data` and `$area` available

`$data` is used to access the module's data in the template.

`$area` is used to include the HTML of an area's modules into the modules template itself.
