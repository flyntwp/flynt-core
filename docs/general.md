# Flynt Core

The Flynt core plugin is the basic building block of the Flynt WordPress Framework.
It offers a tiny public interface and a few WordPress hooks for achieving the main principles and ideas behind the framework.

## Main principles

The main functionaly of the Flynt Core can be seen as a HTML generator. Given a minimal configuration this plugin first creates a so called construction plan and then renders it.

### Config

The starting point of the whole process is a configuration that represents a module. Modules can be nested through so called areas.

The available properties are:

| Property | Description |
| :------: | ----------- |
| **name**<br>*(string)* | *(required)* name of the module |
| **dataFilter**<br>*(string)* | a Wordpress filter that will be called to retrieve the module's data |
| **dataFilterArgs**<br>*(array)* | arguments to pass to the *dataFilter* |
| **customData**<br>*(array/object)* | pass custom data to the module. When used with *dataFilter* on the same module, it will the data will be merged. When used alone it will replace the modules data. |
| **parentData**<br>*(array/object)* | replace parent data of a module (only relevant for advanced use cases) |
| **areas**<br>*(array/object of arrays)* | defines a module's child modules grouped into named areas. The key is the area name, the value is an array of modules. |

### Build the construction plan

From the config Flynt core will build a construction plan by doing the following steps recursively:

1. **Initialize empty module data**

  `$config['data']` is set to an empty array. This is the starting point for every creation. Thus you cannot pass `data` through the config directly. Use `customData` instead.

2. **Set parent data to either the *parentData* specified in the config, the module's parent's data, or an empty array**

  The parent data is passed to multiple filters that will be called later in the building process. It is determined either by `parentData` specified in the config or by the actual data from the module's parent. Can be an empty array.

3. **apply *dataFilter* with *dataFilterArgs* to module data**

  The first situation where data can be assigned to the module itself. A filter with the name passed to `dataFilter` will be applied with the initially empty module data and the `dataFilterArgs`. This can be an arbitrary name but we advice to namespace / prefix it with e.g. `Flynt/DataFiltes/`.

4. **merge *customData* into module data**

  In order to add additional data or replace certain data retrieved by the `dataFilter`, `customData` can be added through the config. This will be merged with the existing module data.

5. **apply general filter *Flynt/modifyModuleData***

  In the previous steps the module data was determined by values passed through the config. This filter is applied for every module. Every filter you add here will be applied to every module. This make it the designated place to add default data that you want to access in every module.

6. **apply module specific filter *Flynt/modifyModuleData?name={$moduleName}***

  This filter targets a specific module specified by the `moduleName`. It can be used to do some default data manipulation that is needed for rendering the module. Since no data logic should be added to a template (except simple loops or control statements) every preparation, formating, etc. should be done here. This filter will usually be added in a modules *function.php*.

7. **apply module specific filter *Flynt/dynamicSubmodules?name={$moduleName}***

  This can be used to add sub modules (modules in an area) to a module. It is useful for adding a sub module based on data that comes from a data filter.

8. **do the same for submodule specified in and dynamically added to *areas***

  The final step for one module is doing the same construction logic for each of the module's area's sub modules.

### Render construction plan

The construction plan contains all the information needed to be rendered. The recursive rendering includes the following steps:

1. **render construction plan for areas**

  The recursive rendering starts by traversing down the module's areas rendering each module and joining the modules' rendering output to one HTML string for each area.

2. **apply general filter *WPStarter/renderModule***

  This filter is called for every module. This is the designated place to define general rendering rules like e.g. integrating a template engine.

3. **apply module specific filter *WPStarter/renderModule?name={$moduleName}***

  This filter can be used to target module specific rendering.
