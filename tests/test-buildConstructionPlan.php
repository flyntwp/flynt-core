<?php
/**
 * Class BuildConstructionPlanTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Build Construction Plan test case.
 */

require_once dirname(__DIR__) . '/lib/WPStarter/BuildConstructionPlan.php';

use WPStarter\TestCase;
use WPStarter\BuildConstructionPlan;
use Brain\Monkey\WP\Filters;

class BuildConstructionPlanTest extends TestCase {

  function setUp() {
    parent::setUp();

    $this->moduleList = [
      'DynamicModule' => '',
      'SingleModule' => '',
      'ModuleWithArea' => '',
      'NestedModuleWithArea' => '',
      'ModuleInConfigFile' => '',
      'ChildModuleInConfigFile' => '',
      'GrandChildA' => '',
      'GrandChildB' => '',
      'GrandChildC' => ''
    ];
  }

  function testShowWarningOnEmptyConfig() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    $cp = BuildConstructionPlan::fromConfig([]);
  }

  function testReturnsEmptyConstructionPlanOnEmptyConfig() {
    $cp = @BuildConstructionPlan::fromConfig([]);
    $this->assertEquals($cp, []);
  }

  function testShowWarningOnMissingNameInConfig() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    $cp = BuildConstructionPlan::fromConfig([
      'data' => [
        'whatever'
      ]
    ]);
  }

  function testReturnsEmptyConstructionPlanOnMissingNameInConfig() {
    $cp = @BuildConstructionPlan::fromConfig([
      'data' => [
        'whatever'
      ]
    ]);
    $this->assertEquals($cp, []);
  }

  function testShowWarningIfConfigIsAnObject() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    $cp = BuildConstructionPlan::fromConfig(new StdClass());
  }

  function testReturnsEmptyConstructionPlanIfConfigIsAnObject() {
    $cp = @BuildConstructionPlan::fromConfig(new StdClass());
    $this->assertEquals($cp, []);
  }

  function testShowWarningIfConfigIsAString() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    $cp = BuildConstructionPlan::fromConfig('string');
  }

  function testReturnsEmptyConstructionPlanIfConfigIsAString() {
    $cp = @BuildConstructionPlan::fromConfig('string');
    $this->assertEquals($cp, []);
  }

  function testShowWarningIfConfigIsANumber() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    $cp = BuildConstructionPlan::fromConfig(0);
  }

  function testReturnsEmptyConstructionPlanIfConfigIsANumber() {
    $cp = @BuildConstructionPlan::fromConfig(0);
    $this->assertEquals($cp, []);
  }

  function testShowWarningWhenModuleIsNotRegistered() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    BuildConstructionPlan::fromConfig([
      'name' => 'ThisModuleIsNotRegistered'
    ]);
  }

  function testReturnsEmptyConstructionPlanWhenModuleIsNotRegistered() {
    $cp = @BuildConstructionPlan::fromConfig([
      'name' => 'ThisModuleIsNotRegistered'
    ]);
    $this->assertEquals($cp, []);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testConfigCanBeLoadedFromFile() {
    $fileName = 'exampleConfig.json';
    $filePath = TestHelper::getConfigPath() . $fileName;

    Filters::expectApplied('WPStarter/configPath')
    ->andReturn($filePath);

    Filters::expectApplied('WPStarter/configFileLoader')
    ->once()
    ->with(null, $fileName, $filePath)
    ->andReturn([
      'name' => 'SingleModule'
    ]);

    $this->mockModuleManager();

    $cp = BuildConstructionPlan::fromConfigFile($fileName);
    $this->assertEquals($cp, [
      'name' => 'SingleModule',
      'data' => []
    ]);
  }

  function testShowWarningWhenConfigFileDoesntExist() {
    $fileName = 'exceptionTest.json';

    Filters::expectApplied('WPStarter/configPath')
    ->once()
    ->with(null, $fileName)
    ->andReturn('/not/a/real/config/file.json');

    $this->expectException('PHPUnit_Framework_Error_Warning');

    $cp = BuildConstructionPlan::fromConfigFile($fileName);
  }

  function testReturnsEmptyConstructionPlanWhenConfigFileDoesntExist() {
    $fileName = 'exceptionTest.json';

    Filters::expectApplied('WPStarter/configPath')
    ->once()
    ->with(null, $fileName)
    ->andReturn('/not/a/real/config/file.json');

    $cp = @BuildConstructionPlan::fromConfigFile($fileName);
    $this->assertEquals($cp, []);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testModuleWithoutDataIsValid() {
    $module = TestHelper::getCustomModule('SingleModule', ['name', 'areas']);

    $this->mockModuleManager();

    $cp = BuildConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => 'SingleModule',
      'data' => []
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testModuleDataIsFiltered() {
    $moduleName = 'SingleModule';

    // Params: ModuleName, hasFilterArgs = false, returnDuplicate = false
    TestHelper::registerDataFilter($moduleName);

    $module = TestHelper::getCustomModule($moduleName, ['name', 'dataFilter', 'areas']);

    $this->mockModuleManager();

    $cp = BuildConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $moduleName,
      'data' => [
        'test' => 'result'
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testDataFilterArgumentsAreUsed() {
    $moduleName = 'SingleModule';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($moduleName, true, false);

    $module = TestHelper::getCustomModule($moduleName, ['name', 'dataFilter', 'dataFilterArgs', 'areas']);

    $this->mockModuleManager();

    $cp = BuildConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $moduleName,
      'data' => [
        'test' => 'result'
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testCustomDataIsAddedToModule() {
    $moduleName = 'SingleModule';

    // this simulates add_filter with return data:
    $module = TestHelper::getCustomModule($moduleName, ['name', 'customData', 'areas']);

    $this->mockModuleManager();

    $cp = BuildConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $moduleName,
      'data' => [
        'test0' => 0,
        'test1' => 'string',
        'test2' => [
          'something strange'
        ],
        'duplicate' => 'newValue'
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testDataIsFilteredAndCustomDataIsAdded() {
    $moduleName = 'SingleModule';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($moduleName, false, true);

    $module = TestHelper::getCustomModule($moduleName, ['name', 'dataFilter', 'customData', 'areas']);

    $this->mockModuleManager();

    $cp = BuildConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $moduleName,
      'data' => [
        'test' => 'result',
        'test0' => 0,
        'test1' => 'string',
        'test2' => [
          'something strange'
        ],
        'duplicate' => 'newValue'
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testModifyModuleDataFiltersAreApplied() {
    // Made this more complex than necessary to also test parentData being passed
    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'SingleModule';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($parentModuleName);

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($childModuleName, true, true);

    $parentModule = TestHelper::getCustomModule($parentModuleName, ['name', 'dataFilter', 'areas']);
    $childModule = TestHelper::getCompleteModule($childModuleName);

    $parentModule['areas'] = [
      'Area51' => [
        $childModule
      ]
    ];

    $this->mockModuleManager();

    $parentData = [
      'test' => 'result'
    ];

    $childData = [
      'test' => 'result',
      'test0' => 0,
      'test1' => 'string',
      'test2' => [
        'something strange'
      ],
      'duplicate' => 'newValue'
    ];

    $newChildData = array_merge($childData, [
      'test' => 'fromAddData',
      'something' => 'else'
    ]);

    $parentModuleAsArg = array_merge($parentModule, [
      'data' => $parentData
    ]);
    unset($parentModuleAsArg['dataFilter']);
    unset($parentModuleAsArg['dataFilterArgs']);
    unset($parentModuleAsArg['customData']);

    $childModuleAsArg = array_merge($childModule, [
      'data' => $childData
    ]);
    unset($childModuleAsArg['dataFilter']);
    unset($childModuleAsArg['dataFilterArgs']);
    unset($childModuleAsArg['customData']);

    Filters::expectApplied('WPStarter/modifyModuleData')
    ->with($parentData, [], $parentModuleAsArg)
    ->ordered()
    ->once()
    ->andReturn($parentData);

    Filters::expectApplied('WPStarter/modifyModuleData')
    ->with($childData, $parentData, $childModuleAsArg)
    ->ordered()
    ->once()
    ->andReturn($childData);

    Filters::expectApplied("WPStarter/modifyModuleData?name={$childModuleName}")
    ->with($childData, $parentData, $childModuleAsArg)
    ->once()
    ->andReturn($newChildData);

    $cp = BuildConstructionPlan::fromConfig($parentModule, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $parentModuleName,
      'data' => [
        'test' => 'result'
      ],
      'areas' => [
        'Area51' => [
          [
            'name' => $childModuleName,
            'data' => [
              'test' => 'fromAddData',
              'something' => 'else',
              'test0' => 0,
              'test1' => 'string',
              'test2' => [
                'something strange'
              ],
              'duplicate' => 'newValue'
            ]
          ]
        ]
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testNestedModuleIsAddedToArea() {
    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'SingleModule';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($childModuleName, true, true);

    $module = TestHelper::getCustomModule($parentModuleName, ['name', 'areas']);

    $module['areas'] = [
      'Area51' => [
        TestHelper::getCompleteModule($childModuleName)
      ]
    ];

    $this->mockModuleManager();

    $cp = BuildConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $parentModuleName,
      'data' => [],
      'areas' => [
        'Area51' => [
          [
            'name' => $childModuleName,
            'data' => [
              'test' => 'result',
              'test0' => 0,
              'test1' => 'string',
              'test2' => [
                'something strange'
              ],
              'duplicate' => 'newValue'
            ]
          ]
        ]
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testParentModuleDataIsNotAddedToChildModule() {
    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'SingleModule';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($parentModuleName, false, false);

    $module = TestHelper::getCustomModule($parentModuleName, ['name', 'dataFilter', 'areas']);

    $module['areas'] = [
      'area51' => [
        TestHelper::getCustomModule($childModuleName, ['name'])
      ]
    ];

    $this->mockModuleManager();

    $cp = BuildConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $parentModuleName,
      'data' => [
        'test' => 'result',
      ],
      'areas' => [
        'area51' => [
          [
            'name' => $childModuleName,
            'data' => []
          ]
        ]
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testParentDataIsOverwritten() {
    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'SingleModule';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($parentModuleName, false, false);

    $newParentData = [
      'custom' => 'parentData'
    ];

    $module = TestHelper::getCustomModule($parentModuleName, ['name', 'dataFilter', 'areas']);
    $childModule = TestHelper::getCustomModule($childModuleName, ['name']);
    $childModule['parentData'] = $newParentData;

    $module['areas'] = [
      'area51' => [
        $childModule
      ]
    ];

    $this->mockModuleManager();

    Filters::expectApplied('WPStarter/modifyModuleData')
    ->with(['test' => 'result'], [], Mockery::type('array'))
    ->ordered()
    ->once()
    ->andReturn(['test' => 'result']);

    Filters::expectApplied('WPStarter/modifyModuleData')
    ->with([], $newParentData, Mockery::type('array'))
    ->ordered()
    ->once()
    ->andReturn($newParentData);

    $cp = BuildConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $parentModuleName,
      'data' => [
        'test' => 'result',
      ],
      'areas' => [
        'area51' => [
          [
            'name' => $childModuleName,
            'data' => [
              'custom' => 'parentData'
            ]
          ]
        ]
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testDeeplyNestedModulesCreateValidConstructionPlan() {
    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'NestedModuleWithArea';
    $grandChildModuleNameA = 'GrandChildA';
    $grandChildModuleNameB = 'GrandChildB';
    $grandChildModuleNameC = 'GrandChildC';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($parentModuleName, false, false);

    $module = TestHelper::getCustomModule($parentModuleName, ['name', 'dataFilter', 'areas']);

    $module['areas'] = [
      'area51' => [
        TestHelper::getCustomModule($childModuleName, ['name', 'areas'])
      ]
    ];

    $module['areas']['area51'][0]['areas'] = [
      'district9' => [
        TestHelper::getCustomModule($grandChildModuleNameA, ['name'])
      ],
      'alderaan' => [
        TestHelper::getCustomModule($grandChildModuleNameB, ['name']),
        TestHelper::getCustomModule($grandChildModuleNameC, ['name'])
      ]
    ];

    $this->mockModuleManager();

    $cp = BuildConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $parentModuleName,
      'data' => [
        'test' => 'result',
      ],
      'areas' => [
        'area51' => [
          [
            'name' => $childModuleName,
            'data' => [],
            'areas' => [
              'district9' => [
                [
                  'name' => $grandChildModuleNameA,
                  'data' => []
                ]
              ],
              'alderaan' => [
                [
                  'name' => $grandChildModuleNameB,
                  'data' => []
                ],
                [
                  'name' => $grandChildModuleNameC,
                  'data' => []
                ]
              ]
            ]
          ]
        ]
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testDynamicSubmodulesCanBeAddedWithAFilter() {
    $moduleName = 'ModuleWithArea';
    $dynamicModuleName = 'SingleModule';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($moduleName, false, false);

    $module = TestHelper::getCustomModule($moduleName, ['name', 'dataFilter', 'areas']);
    $dynamicModule = TestHelper::getCustomModule($dynamicModuleName, ['name']);

    Filters::expectApplied("WPStarter/dynamicSubmodules?name={$moduleName}")
    ->with([], ['test' => 'result'], [])
    ->once()
    ->andReturn(['area51' => [ $dynamicModule ]]);

    $this->mockModuleManager();

    $cp = BuildConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $moduleName,
      'data' => [
        'test' => 'result'
      ],
      'areas' => [
        'area51' => [
          [
            'name' => $dynamicModuleName,
            'data' => []
          ]
        ]
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testDynamicSubmodulesReceiveParentData() {
    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'NestedModuleWithArea';
    $childSubmoduleName = 'SingleModule';
    $dynamicModuleName = 'DynamicModule';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($parentModuleName, false, false);

    $parentModule = TestHelper::getCustomModule($parentModuleName, ['name', 'dataFilter', 'areas']);
    $childModule = TestHelper::getCustomModule($childModuleName, ['name']);
    $childSubmodule = TestHelper::getCustomModule($childSubmoduleName, ['name']);
    $dynamicModule = TestHelper::getCustomModule($dynamicModuleName, ['name']);

    $childModule['areas'] = [
      'childArea' => [ $childSubmodule ]
    ];
    $parentModule['areas'] = [
      'parentArea' => [ $childModule ]
    ];

    Filters::expectApplied("WPStarter/dynamicSubmodules?name={$childSubmoduleName}")
    ->with([], [], ['test' => 'result'])
    ->once()
    ->andReturn(['area51' => [ $dynamicModule ]]);

    $this->mockModuleManager();

    $cp = BuildConstructionPlan::fromConfig($parentModule, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $parentModuleName,
      'data' => [
        'test' => 'result'
      ],
      'areas' => [
        'parentArea' => [
          [
            'name' => $childModuleName,
            'data' => [],
            'areas' => [
              'childArea' => [
                [
                  'name' => $childSubmoduleName,
                  'data' => [],
                  'areas' => [
                    'area51' => [
                      [
                        'name' => $dynamicModuleName,
                        'data' => []
                      ]
                    ]
                  ]
                ]
              ]
            ]
          ]
        ]
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testAppliesInitModuleConfigFilters() {
    $moduleName = 'ModuleWithArea';
    $childModuleName = 'SingleModule';

    $moduleData = ['test' => 'result'];

    $moduleConfig = TestHelper::getCustomModule($moduleName, ['name', 'areas']);
    $childModuleConfig = TestHelper::getCustomModule($childModuleName, ['name']);

    $moduleConfig['areas']['area51'][0] = $childModuleConfig;

    $moduleConfigFilterParam = $moduleConfig;
    $childModuleConfigFilterParam = $childModuleConfig;

    $moduleConfigFilterParam['data'] = [];
    $childModuleConfigFilterParam['data'] = [];

    $moduleConfigAfterInit = array_merge($moduleConfigFilterParam, ['data' => $moduleData]);

    Filters::expectApplied('WPStarter/initModuleConfig')
    ->with($moduleConfigFilterParam, null, [])
    ->ordered()
    ->once()
    ->andReturn($moduleConfigAfterInit);

    Filters::expectApplied('WPStarter/initModuleConfig')
    ->with($childModuleConfigFilterParam, 'area51', $moduleData)
    ->ordered()
    ->once()
    ->andReturn($childModuleConfigFilterParam);

    Filters::expectApplied("WPStarter/initModuleConfig?name={$moduleName}")
    ->with($moduleConfigAfterInit, null, [])
    ->once()
    ->andReturn($moduleConfigAfterInit);

    Filters::expectApplied("WPStarter/initModuleConfig?name={$childModuleName}")
    ->with($childModuleConfigFilterParam, 'area51', $moduleData)
    ->once()
    ->andReturn($moduleConfigAfterInit);

    $this->mockModuleManager();

    BuildConstructionPlan::fromConfig($moduleConfig);
  }

  // Helpers
  function mockModuleManager() {
    $moduleManagerMock = Mockery::mock('ModuleManager');

    Mockery::mock('alias:WPStarter\ModuleManager')
    ->shouldReceive('getInstance')
    ->andReturn($moduleManagerMock);

    $moduleManagerMock
    ->shouldReceive('getAll')
    ->andReturn($this->moduleList);
  }
}
