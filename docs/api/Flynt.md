# Flynt (namespace)

This is the main namespace of the plugin. In your theme you should only need to call the functions contained in the namespace. The following public functions are available:

## initDefaults
```php
function initDefaults()
```
Initializes a set of defaults used for regular setups.

## registerModule
```php
function registerModule(string $moduleName, string $modulePath = null)
```
Registers a module for later use. If no `$modulePath` is specified, the default will be taken.

```php
function registerModules(array $modules = [])
```
Registers an array of modules for later use. The array can consist of module names only (as values), or with the module name as key and module path as value.

## (echo|get)HtmlFromConfig
```php
function echoHtmlFromConfig(array $config)
```
```php
function getHtmlFromConfig(array $config)
```
Return and optionally echo the HTML generated from processing a given configuration array.

## (echo|get)HtmlFromConfigFile
```php
function echoHtmlFromConfigFile(string $fileName)
```
```php
function getHtmlFromConfigFile(string $fileName)
```
Return and optionally echo the HTML generated from processing a given configuration file.
