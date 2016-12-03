# BuildConstructionPlan (class)

Used internally to create a complete construction plan from a minimal config.

## fromConfig (static)

Build the construction plan from a config array.

```php
public static function fromConfig (array $config)
```

## fromConfigFile (static)

Build the construction plan from a config file. Uses filters `Flynt/configPath` and
`Flynt/configFileLoader` to determine file path and loading logic.

```php
public static function fromConfigFile (string $configFileName)
```
