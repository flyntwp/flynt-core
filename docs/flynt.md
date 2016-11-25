# Public API

## Table of Contents
1. [References](#1.-references)

## 1. References

### Available Functions
#### echoHtmlFromConfig
Echoes result of `getHtmlFromConfig`.

#### echoHtmlFromConfigFile
Echoes result of `getHtmlFromConfigFile`.

#### getHtmlFromConfig
Returns rendered html using a config array as the only parameter. Uses BuildConstructionPlan and Render classes.

#### getHtmlFromConfigFile
Returns rendered html using a config file name as the only parameter. Uses BuildConstructionPlan and Render classes.

Make sure to either use the default `{theme-directory}/config/` path as the parent folder or define your own config path using the filters specified in the [BuildConstructionPlan](BuildConstructionPlan.md) class. You will also find examples to load configuration files in different formats than the default `.json` format.

#### registerModule
Registers a module to be rendered and loads the module's functions.php if present. This behaviour can be adjusted and extended using the actions defined in the [ModuleManager](ModuleManager.md) class.
