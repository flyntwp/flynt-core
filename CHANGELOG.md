# Change Log

All notable changes to this project will be documented in this file. See [standard-version](https://github.com/conventional-changelog/standard-version) for commit guidelines.

<a name="1.0.0"></a>
# 1.0.0 (2017-03-27)


### Bug Fixes

* **BuildConstructionPlan:** always create valid construction plan ([55376f8](https://github.com/flyntwp/flynt-core/commit/55376f8))
* **constructionPlan:** changed exceptions to warnings ([e6c4542](https://github.com/flyntwp/flynt-core/commit/e6c4542))
* **constructionPlan:** do not require non-empty areas for dynamicSubmodules + refactor names ([af34a92](https://github.com/flyntwp/flynt-core/commit/af34a92))
* **defaultLoader:** corrected naming for registerModule hook, added correct exception tests for warn ([254b860](https://github.com/flyntwp/flynt-core/commit/254b860))
* **defaultLoader:** corrected use to be use function for the helper function ([d1f2a8c](https://github.com/flyntwp/flynt-core/commit/d1f2a8c))
* **defaults:** missing argument 4 areaHtml ([cf855a0](https://github.com/flyntwp/flynt-core/commit/cf855a0))
* **render:** changed exceptions to warnings ([c7230c8](https://github.com/flyntwp/flynt-core/commit/c7230c8))
* **Render:** Error messages ([697aca6](https://github.com/flyntwp/flynt-core/commit/697aca6))
* **renderer:** throw exception when template not found ([e940ff5](https://github.com/flyntwp/flynt-core/commit/e940ff5))
* **wpStarter:** register renamed to correct: registerModule ([8c329e0](https://github.com/flyntwp/flynt-core/commit/8c329e0))


### Features

* **buildConstructionPlan:** added initModuleConfig filter ([09717f9](https://github.com/flyntwp/flynt-core/commit/09717f9))
* **buildConstructionPlan:** added modifyModuleData filters ([369ba9b](https://github.com/flyntwp/flynt-core/commit/369ba9b))
* **buildConstructionPlan:** added parentData overwrite ([c3e5a46](https://github.com/flyntwp/flynt-core/commit/c3e5a46))
* **ComponentManager:** added isRegistered ([47a1c35](https://github.com/flyntwp/flynt-core/commit/47a1c35))
* **ComponentManager:** pass name as second parameter for component-specific registerComponent actio ([b7def8d](https://github.com/flyntwp/flynt-core/commit/b7def8d))
* **composer:** add composer installers and type wordpress plugin ([406fb71](https://github.com/flyntwp/flynt-core/commit/406fb71))
* **composer:** add composer package info ([b7f0707](https://github.com/flyntwp/flynt-core/commit/b7f0707))
* **composer:** autoload flynt-core-plugin.php ([c9ff920](https://github.com/flyntwp/flynt-core/commit/c9ff920))
* **constructionPlan:** added default path for config + adjusted tests, refactored getTemplateDirect ([5da250d](https://github.com/flyntwp/flynt-core/commit/5da250d))
* **constructionPlan:** added filter hook for fromConfigFile loading, added additional checks for co ([100b5bc](https://github.com/flyntwp/flynt-core/commit/100b5bc))
* **constructionPlan:** added load from config file (json) ([a08b028](https://github.com/flyntwp/flynt-core/commit/a08b028))
* **constructionPlan:** apply WPStarter/dynamicSubmodules filter ([1368641](https://github.com/flyntwp/flynt-core/commit/1368641))
* **constructionPlan:** nested dynamic submodules ([c30fa87](https://github.com/flyntwp/flynt-core/commit/c30fa87))
* **ConstructionPlan:** added first tests and basic functionality for single module config ([1995b79](https://github.com/flyntwp/flynt-core/commit/1995b79))
* **defaultLoader:** moved default functionality for config file loading and rendering to its own cl ([51f9ff8](https://github.com/flyntwp/flynt-core/commit/51f9ff8))
* **defaults:** added getModulesDirectory method for default modules path ([37af61d](https://github.com/flyntwp/flynt-core/commit/37af61d))
* **helpers:** add extractNestedDataFromArray helper function ([021dbe7](https://github.com/flyntwp/flynt-core/commit/021dbe7))
* **helpers:** extract data helper now also works with objects ([74d575a](https://github.com/flyntwp/flynt-core/commit/74d575a))
* **helpers:** extractData - added ability to use array further down the param line ([966cd77](https://github.com/flyntwp/flynt-core/commit/966cd77))
* **moduleManager:** added ModuleManager Class, refactored WPStarter Class into global namespaced fu ([23ba863](https://github.com/flyntwp/flynt-core/commit/23ba863))
* **render:** added correct params to renderModule filters and refactored class + tests ([4b07a64](https://github.com/flyntwp/flynt-core/commit/4b07a64))
* **render:** use null as default arg in renderModule filter ([3e80add](https://github.com/flyntwp/flynt-core/commit/3e80add))
* **renderer:** add basic render functionality for single and nested modules ([b8003f7](https://github.com/flyntwp/flynt-core/commit/b8003f7))
* **renderer:** added renderModule hook ([d071f48](https://github.com/flyntwp/flynt-core/commit/d071f48))
* **renderer:** added single module render hook ([f1edd70](https://github.com/flyntwp/flynt-core/commit/f1edd70))
* **wpStarter:** added get_template_directory to modulesPath filter and renamed defaultModulesPath t ([c37bb88](https://github.com/flyntwp/flynt-core/commit/c37bb88))
* **wpStarter:** added missing getModuleFilePath method ([be73b5d](https://github.com/flyntwp/flynt-core/commit/be73b5d))
* **wpStarter:** added registerModule actions ([741a667](https://github.com/flyntwp/flynt-core/commit/741a667))
* **wpStarter:** added registerModule functionality + shortcut methods for public API ([98fa7be](https://github.com/flyntwp/flynt-core/commit/98fa7be))
* **wpStarter:** added registerModules ([bec7863](https://github.com/flyntwp/flynt-core/commit/bec7863))
* **wpStarter:** registerModule optional argument for path ([d234e28](https://github.com/flyntwp/flynt-core/commit/d234e28))
