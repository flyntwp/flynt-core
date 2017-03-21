<?php

namespace Flynt\Tests;

use Brain\Monkey\WP\Filters;

class TestHelper
{
    public static function getCompleteComponent($componentName = 'ComponentName')
    {
        return [
        'name' =>  $componentName,
        'customData' => [
        'test0' => 0,
        'test1' => 'string',
        'test2' => [
          'something strange'
        ],
        'duplicate' => 'newValue'
        ],
        'areas' => []
        ];
    }

  // how to use: TestHelper::getCustomComponent('Component', ['name', 'dataFilter'])
    public static function getCustomComponent($componentName = 'Component', $config = [])
    {
        return array_filter(self::getCompleteComponent($componentName), function ($key) use ($config) {
            return in_array($key, $config);
        }, ARRAY_FILTER_USE_KEY);
    }

    public static function getComponentIndexPath($componentName)
    {
        return self::getComponentPath(null, $componentName) . '/index.php';
    }

    public static function getConfigPath()
    {
        return __DIR__ . "/assets/";
    }

    public static function getComponentsPath()
    {
        return __DIR__ . '/assets/src/';
    }

    public static function getComponentPath($componentPath, $componentName)
    {
        if (is_null($componentPath)) {
            return __DIR__ . '/assets/src/' . $componentName;
        }
        return $componentPath;
    }

    public static function getTemplateDirectory()
    {
        return __DIR__ . "/assets";
    }

    public static function trailingSlashIt($string)
    {
        return rtrim($string, '/\\') . '/';
    }
}
