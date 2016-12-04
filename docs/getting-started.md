# Getting Started

Getting started with the Flynt Core plugin is a simple two step process:

1. Install the Flynt Core plugin into the standard Wordpress folder.
2. Active the plugin in the Wordpress Administration "Plugins" panel.

With this done, you're good to go! This is the complete setup required to use the Flynt Core plugin.

However, to quickly enjoy all of the benefits offered by the Flynt Framework, we strongly recommend using the official [Flynt Theme](/add-link-here).

## First steps

The simplest way of using Flynt Core can be demonstrated with the following *Hello, world* example:

In your theme's `index.php` add:

```php
Flynt\echoHtmlFromConfig([
  'name' => 'HelloWorld'
]);
```

...and to your theme's `functions.php`:

```php
add_filter('Flynt\renderModule?name=HelloWorld', function () {
  return 'Hello, world!';
});
```

That's it! You have successfully used Flynt Core for the first time.

## Next steps

We can take this a step further by initializing the plugins defaults. This will:
- Implement the intended module structure.
- Load additional module scripts.
- Enable PHP file rendering.

To do so, add the following line of code to your theme's `functions.php`:

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

In summary, these hooks do the following:
- Load config files from the `./config` directory.
- Parse `.json` config files.
- Set the module path to `./Modules`.
- Require modules to be registered.
- Load `./Modules/{$moduleName}/function.php` from every registered module, if the file exists.
- Render `./Modules/{$moduleName}/index.php` and make view helper function `$data` and `$area` available.
  - `$data` is used to access the module's data in the view template.
  - `$area` is used to include the HTML of an area's modules into the modules template itself.
