# flynt-core

[![standard-readme compliant](https://img.shields.io/badge/readme%20style-standard-brightgreen.svg?style=flat-square)](https://github.com/RichardLitt/standard-readme)

<!-- TODO: Put more badges here. -->

> The basic building block of the Flynt Framework.

The Flynt Core Plugin offers a small public interface in combination with a few WordPress hooks to achieve the main principles and ideas behind the Flynt Framework.

## Table of Contents

- [Background](#background)
- [Install](#install)
- [Usage](#usage)
- [Maintainers](#maintainers)
- [Contribute](#contribute)
- [License](#license)

## Background

The main functionality of the Flynt Core plugin can be seen as a HTML generator. Given a minimal configuration the Flynt Core plugin first creates a "Construction Plan" and then renders it.

The starting point for the entire process is a configuration representing a component. Multiple components can then be nested through the use of areas.

## Install

<!-- TODO: install via WordPress instructions -->

To install via composer, run:

```bash
composer require flyntwp/flynt-core
```

## Usage

The simplest way of using Flynt Core can be demonstrated with the following *Hello, world* example:

In your theme's `index.php` add:

```php
Flynt\echoHtmlFromConfig([
  'name' => 'HelloWorld'
]);
```

...and to your theme's `functions.php`:

```php
add_filter('Flynt\renderComponent?name=HelloWorld', function () {
  return 'Hello, world!';
});
```

We can take this a step further by initializing the plugin's defaults. This will:
- Implement the intended component structure.
- Load additional component scripts.
- Enable PHP file rendering.

To do so, add the following line of code to your theme's `functions.php`:

```php
Flynt\initDefaults();
```

This will add the following hooks:

```php
add_filter('Flynt/configPath', ['Flynt\Defaults', 'setConfigPath'], 999, 2);
add_filter('Flynt/configFileLoader', ['Flynt\Defaults', 'loadConfigFile'], 999, 3);
add_filter('Flynt/componentPath', ['Flynt\Defaults', 'setComponentPath'], 999, 2);
add_action('Flynt/registerComponent', ['Flynt\Defaults', 'loadFunctionsFile']);
add_filter('Flynt/renderComponent', ['Flynt\Defaults', 'renderComponent'], 999, 4);
```

In summary, these hooks do the following:
- Load config files from the `./config` directory.
- Parse `.json` config files.
- Set the component path to `./Components`.
- Load `./Components/{$componentName}/functions.php` from every registered component, if the file exists.
- Render `./Components/{$componentName}/index.php` and make view helper functions `$data` and `$area` available.
  - `$data` is used to access the component's data in the view template.
  - `$area` is used to include the HTML of an area's components into the components template itself.


**More documentation coming soon...**

<!-- TODO: add link to documentation for more information -->

## Maintainers

This project is maintained by [bleech](https://github.com/bleech).

The main people in charge of this repo are:

- [Dominik Tränklein](https://github.com/domtra)
- [Doğa Gürdal](https://github.com/Qakulukiam)

## Contribute

To contribute, please use github [issues](https://github.com/flyntwp/flynt-core/issues). Pull requests are accepted.

Small note: If editing the README, please conform to the [standard-readme](https://github.com/RichardLitt/standard-readme) specification.

## License

MIT © [bleech](https://www.bleech.de)
