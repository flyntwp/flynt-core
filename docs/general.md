# Flynt Core

The Flynt Core plugin is the basic building block of the Flynt Framework. Flynt Core offers a small public interface in combination with a few WordPress hooks to achieve the main principles and ideas behind the framework.

## Main principles

The main functionality of the Flynt Core can be seen as a HTML generator. Given a minimal configuration the Flynt Core plugin first creates a "Construction Plan" and then renders it.

<!-- TODO: Should then either explain the construction plan or link to the section where its explained, or they could think 'what the heck is the construction plan'...?  -->

### Config

The starting point for the entire process is a configuration representing a module. Multiple modules can then be nested through the use of **areas**.

The available properties are as follows:

| Property | Description |
| :------: | ----------- |
| **name**<br>*(string)* | *(required)* name of the module |
| **dataFilter**<br>*(string)* | a Wordpress filter that will be called to retrieve the module's data |
| **dataFilterArgs**<br>*(array)* | arguments to pass to the *dataFilter* |
| **customData**<br>*(array/object)* | pass custom data to the module. When used with *dataFilter* on the same module, it will the data will be merged. When used alone it will replace the modules data. |
| **parentData**<br>*(array/object)* | replace parent data of a module (only relevant for advanced use cases) |
| **areas**<br>*(array/object of arrays)* | defines a module's child modules grouped into named areas. The key is the area name, the value is an array of modules. |

### Build the construction plan

From this configuration file, Flynt Core will then build a Construction Plan by recursively following the below steps:

1. **Initialize empty module data.**

  `$config['data']` is set to an empty array. This is the starting point for every creation. As such, you **cannot** pass `data` through the config directly. Use `customData` instead.

2. **Set parent data to either the *parentData* specified in the config, the module's parent's data, or an empty array.**

  The parent data is passed to multiple filters that will be called later in the building process. It is determined either by `parentData` specified in the config or by the actual data from the module's parent. Can be an empty array.

3. **Apply *dataFilter* with *dataFilterArgs* to module data.**

  The first situation where data can be assigned to the module itself. A filter with the name passed to `dataFilter` will be applied with the initially empty module data and the `dataFilterArgs`. This can be an arbitrary name but we advice to namespace / prefix it with e.g. `Flynt/DataFiltes/`.

4. **Merge *customData* into module data.**

  In order to add additional data or replace certain data retrieved by the `dataFilter`, `customData` can be added through the config. This will be merged with the existing module data.

5. **Apply general filter `Flynt/modifyModuleData`.**

  In the previous steps the module data was determined by values passed through the config. This filter is applied for every module. Every filter you add here will be applied to every module. This make it the designated place to add default data that you want to access in every module.

6. **Apply module specific filter `Flynt/modifyModuleData?name={$moduleName}`.**

  This filter targets a specific module specified by the `moduleName`. It can be used to do some default data manipulation that is needed for rendering the module. Since no data logic should be added to a template (except simple loops or control statements) every preparation, formating, etc. should be done here. This filter will usually be added in a modules *function.php*.

7. **Apply module specific filter `Flynt/dynamicSubmodules?name={$moduleName}`.**

  This can be used to add sub modules (modules in an area) to a module. It is useful for adding a sub module based on data that comes from a data filter.

8. **Do the same for submodule specified in - and dynamically added to - *areas*.**

  The final step for one module is doing the same construction logic for each of the module's area's sub modules.

### Render construction plan

The construction plan contains all the information needed to be rendered. This recursive rendering can be summarized in the following steps:

1. **Render construction plan for areas**

  The recursive rendering starts by traversing down the module's areas rendering each module and joining the modules' rendering output to one HTML string for each area.

2. **Apply general filter `WPStarter/renderModule`**

  This filter is called for every module. This is the designated place to define general rendering rules like e.g. integrating a template engine.

3. **Apply the module specific filter `WPStarter/renderModule?name={$moduleName}`**

  This filter can be used to target module specific rendering.
