# ModuleManager (class)

Singleton used internally to manage registered modules.

## getInstance (static)
Get the singleton of the ModuleManager.
```php
public static function getInstance()
```

## registerModule
Register a module for later use.
```php
public function registerModule(string $moduleName, string $modulePath = null)
```

## getModuleFilePath
Get the path to a module specific file.
```php
public function getModuleFilePath(string $moduleName, string $fileName = 'index.php')
```

## getAll
Get all registered Modules.
```php
public function getAll()
```

## removeAll
Remove all registered Modules.
```php
public function removeAll()
```
