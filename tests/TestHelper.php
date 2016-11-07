<?php

use Brain\Monkey\WP\Filters;

class TestHelper {
  public static function getCompleteModule($moduleName = 'ModuleName') {
    return [
      'name' =>  $moduleName,
      'dataFilter' => 'WPStarter/DataFilters/' . $moduleName . '/foo',
      'dataFilterArgs' => [
        'post',
        'page'
      ],
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

  // how to use: TestHelper::getCustomModule('Module', ['name', 'dataFilter'])
  public static function getCustomModule($moduleName = 'Module', $config = []) {
    return array_filter(self::getCompleteModule($moduleName), function ($key) use ($config) {
      return in_array($key, $config);
    }, ARRAY_FILTER_USE_KEY);
  }

  public static function registerDataFilter($moduleName = 'Module', $hasFilterArgs = false, $returnDuplicate = false) {
    $filterArgs = [[]];
    $return = [ 'test' => 'result' ];

    if ($hasFilterArgs) {
      array_push($filterArgs, 'post', 'page');
    }

    if ($returnDuplicate) {
      $return['duplicate'] = 'previousValue';
    }

    // expect apply_filters to be called with 'WPStarter/DataFilters/ModuleName/foo'
    $filterMock = Filters::expectApplied('WPStarter/DataFilters/' . $moduleName . '/foo')
    ->once()
    ->withArgs($filterArgs)
    ->andReturn($return);
  }

  public static function getConfigPath($configName) {
    return __DIR__ . "/assets/{$configName}";
  }

  public static function getModulesPath() {
    return __DIR__ . '/assets/src/';
  }
}
