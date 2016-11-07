<?php
/**
 * Class ConstructionPlanTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Construction plan test case.
 */

use WPStarter\TestCase;
use WPStarter\ConstructionPlan;
use Brain\Monkey\WP\Filters;

class ConstructionPlanTest extends TestCase {

  function setUp() {
    parent::setUp();

    Filters::expectApplied('WPStarter/configPath')
    ->andReturnUsing(['TestHelper', 'getConfigPath']);
  }

  function testThrowErrorOnEmptyConfig() {
    $this->expectException(Exception::class);
    $cp = ConstructionPlan::fromConfig([]);
  }

  function testThrowErrorIfConfigIsAnObject() {
    $this->expectException(Exception::class);
    $cp = ConstructionPlan::fromConfig(new StdClass());
  }

  function testThrowErrorIfConfigIsAString() {
    $this->expectException(Exception::class);
    $cp = ConstructionPlan::fromConfig('string');
  }

  function testThrowErrorIfConfigIsANumber() {
    $this->expectException(Exception::class);
    $cp = ConstructionPlan::fromConfig(0);
  }

  // TODO add test for default paths?
  function testConfigCanBeLoadedFromFile() {
    $cp = ConstructionPlan::fromConfigFile('exampleConfig.json');
    $this->assertEquals($cp, [
      'name' => 'ModuleInConfigFile',
      'data' => [
        'test' => 'test'
      ],
      'areas' => [
        'area51' => [
          0 => [
            'name' => 'ChildModuleInConfigFile',
            'data' => []
          ]
        ]
      ]
    ]);
  }

  function testThrowsErrorWhenConfigFileDoesntExist() {
    Filters::expectApplied('WPStarter/configPath')
    ->with('exceptionTest.json')
    ->andReturn('/not/a/real/file.json');

    $this->expectException(Exception::class);

    $cp = ConstructionPlan::fromConfigFile('exceptionTest.json');
  }

  function testConfigFileLoaderUsesFilterHook() {
    Filters::expectApplied('WPStarter/configFileLoader')
    ->with(null, TestHelper::getConfigPath('exampleConfig.yml'))
    ->once()
    ->andReturn(['name' => 'Module']);

    $cp = ConstructionPlan::fromConfigFile('exampleConfig.yml');
  }

  function testModuleWithoutDataIsValid() {
    $module = TestHelper::getCustomModule('ModuleNoData', ['name', 'areas']);
    $cp = ConstructionPlan::fromConfig($module);
    $this->assertEquals($cp, [
      'name' => 'ModuleNoData',
      'data' => []
    ]);
  }

  function testModuleDataIsFiltered() {
    $moduleName = 'ModuleWithDataFilter';

    // Params: ModuleName, hasFilterArgs = false, returnDuplicate = false
    TestHelper::registerDataFilter($moduleName);

    $module = TestHelper::getCustomModule($moduleName, ['name', 'dataFilter', 'areas']);
    $cp = ConstructionPlan::fromConfig($module);

    $this->assertEquals($cp, [
      'name' => $moduleName,
      'data' => [
        'test' => 'result'
      ]
    ]);
  }

  function testDataFilterArgumentsAreUsed() {
    $moduleName = 'ModuleWithDataFilterArgs';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($moduleName, true, false);

    $module = TestHelper::getCustomModule($moduleName, ['name', 'dataFilter', 'dataFilterArgs', 'areas']);
    $cp = ConstructionPlan::fromConfig($module);

    $this->assertEquals($cp, [
      'name' => $moduleName,
      'data' => [
        'test' => 'result'
      ]
    ]);
  }

  function testCustomDataIsAddedToModule() {
    $moduleName = 'ModuleWithCustomData';

    // this simulates add_filter with return data:
    $module = TestHelper::getCustomModule($moduleName, ['name', 'customData', 'areas']);
    $cp = ConstructionPlan::fromConfig($module);

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

  function testDataIsFilteredAndCustomDataIsAdded() {
    $moduleName = 'ModuleWithDataFilterAndCustomData';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($moduleName, false, true);

    $module = TestHelper::getCustomModule($moduleName, ['name', 'dataFilter', 'customData', 'areas']);
    $cp = ConstructionPlan::fromConfig($module);

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

  function testNestedModuleIsAddedToArea() {
    $childModuleName = 'ModuleNestedChild';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($childModuleName, true, true);

    $module = TestHelper::getCustomModule('ModuleNestedParent', ['name', 'areas']);

    $module['areas'] = [
      'Area51' => [
        TestHelper::getCompleteModule($childModuleName)
      ]
    ];

    $cp = ConstructionPlan::fromConfig($module);

    $this->assertEquals($cp, [
      'name' => 'ModuleNestedParent',
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

  function testParentModuleDataIsNotAddedToChildModule() {
    $parentModuleName = 'ModuleNestedParent';
    $childModuleName = 'ModuleNestedChild';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($parentModuleName, false, false);

    $module = TestHelper::getCustomModule($parentModuleName, ['name', 'dataFilter', 'areas']);

    $module['areas'] = [
      'area51' => [
        TestHelper::getCustomModule($childModuleName, ['name'])
      ]
    ];

    $cp = ConstructionPlan::fromConfig($module);

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

  function testDeeplyNestedModulesCreateValidConstructionPlan() {
    $parentModuleName = 'ModuleNestedParent';
    $childModuleName = 'ModuleNestedChild';
    $grandChildModuleName = 'ModuleNestedGrandChild';

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
        TestHelper::getCustomModule($grandChildModuleName, ['name'])
      ],
      'alderaan' => [
        TestHelper::getCustomModule($grandChildModuleName . '2', ['name']),
        TestHelper::getCustomModule($grandChildModuleName . '3', ['name'])
      ]
    ];

    $cp = ConstructionPlan::fromConfig($module);

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
                  'name' => $grandChildModuleName,
                  'data' => []
                ]
              ],
              'alderaan' => [
                [
                  'name' => $grandChildModuleName . '2',
                  'data' => []
                ],
                [
                  'name' => $grandChildModuleName . '3',
                  'data' => []
                ]
              ]
            ]
          ]
        ]
      ]
    ]);
  }

  function testDynamicSubmodulesCanBeAddedWithAFilter() {
    $moduleName = 'ModuleNestedParent';
    $dynamicModuleName = 'ModuleNestedChild';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($moduleName, false, false);

    $module = TestHelper::getCustomModule($moduleName, ['name', 'dataFilter', 'areas']);
    $dynamicModule = TestHelper::getCustomModule($dynamicModuleName, ['name']);

    Filters::expectApplied("WPStarter/dynamicSubmodules?name={$moduleName}")
    ->with([],  ['test' => 'result'], [])
    ->once()
    ->andReturn(['area51' => [ $dynamicModule ]]);

    $cp = ConstructionPlan::fromConfig($module);

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

  function testDynamicSubmodulesReceiveParentData() {
    $parentModuleName = 'ModuleNestedParent';
    $childModuleName = 'ModuleNestedChild';
    $childSubmoduleName = 'SubmoduleNestedChild';
    $dynamicModuleName = 'ModuleNestedDynamicChild';

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
    ->with([],  [], ['test' => 'result'])
    ->once()
    ->andReturn(['area51' => [ $dynamicModule ]]);

    $cp = ConstructionPlan::fromConfig($parentModule);

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
}
