# How the construction plan is build

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
