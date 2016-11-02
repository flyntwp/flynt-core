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

  // how to use: TestHelper::getCustomModule('ModuleA', ['name', 'dataFilter'])
  public static function getCustomModule($moduleName = 'ModuleName', $config = []) {
    return array_filter(self::getCompleteModule($moduleName), function ($key) use ($config) {
      return in_array($key, $config);
    }, ARRAY_FILTER_USE_KEY);
  }
}
