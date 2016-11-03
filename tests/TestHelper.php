<?php
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
    WP_Mock::userFunction('apply_filters_ref_array', [
      'args' => [
        'WPStarter/DataFilters/' . $moduleName . '/foo', $filterArgs
      ],
      'times' => 1,
      'return' => $return
    ]);
  }

  public static function registerRenderModuleFilter($data, $return = '') {
    $filterName = 'WPStarter/Renderer/renderModule';
    WP_Mock::onFilter($filterName)
    ->with('', $data)
    ->reply($return);
  }

  public static function registerRenderSpecificModuleFilter($data, $module = 'Module', $prevOutput = '', $return = '') {
    $filterName = 'WPStarter/Renderer/renderModule?name=' . $module;
    WP_Mock::onFilter($filterName)
    ->with($prevOutput, $data)
    ->reply($return);
  }
}
