<?php
/**
 * Class ConstructionPlanTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Construction plan test case.
 */

require_once dirname(__DIR__) . '/lib/WPStarter/ConstructionPlan.php';

use WPStarter\TestCase;
use WPStarter\ConstructionPlan;
use Brain\Monkey\WP\Filters;

class ConstructionPlanTest extends TestCase {

  function setUp() {
    parent::setUp();

    $this->moduleList = [
      'DynamicModule' => '',
      'SingleModule' => TestHelper::getModulesPath() . 'SingleModule/',
      'ModuleWithArea' => TestHelper::getModulesPath() . 'ModuleWithArea/',
      'NestedModuleWithArea' => '',
      'ModuleInConfigFile' => '',
      'ChildModuleInConfigFile' => '',
      'GrandChildA' => '',
      'GrandChildB' => '',
      'GrandChildC' => ''
    ];
  }

  function testThrowErrorOnEmptyConfig() {
    $this->expectException(Exception::class);
    $cp = ConstructionPlan::fromConfig([], $this->moduleList);
  }

  function testThrowErrorIfConfigIsAnObject() {
    $this->expectException(Exception::class);
    $cp = ConstructionPlan::fromConfig(new StdClass(), $this->moduleList);
  }

  function testThrowErrorIfConfigIsAString() {
    $this->expectException(Exception::class);
    $cp = ConstructionPlan::fromConfig('string', $this->moduleList);
  }

  function testThrowErrorIfConfigIsANumber() {
    $this->expectException(Exception::class);
    $cp = ConstructionPlan::fromConfig(0, $this->moduleList);
  }

  // TODO add test for default paths?
  function testConfigCanBeLoadedFromFile() {
    $parentModuleName = 'ModuleInConfigFile';
    $childModuleName = 'ChildModuleInConfigFile';

    $cp = ConstructionPlan::fromConfigFile('exampleConfig.json', $this->moduleList);
    $this->assertEquals($cp, [
      'name' => $parentModuleName,
      'data' => [
        'test' => 'test'
      ],
      'path' => $this->moduleList[$parentModuleName],
      'areas' => [
        'area51' => [
          0 => [
            'name' => $childModuleName,
            'data' => [],
            'path' => $this->moduleList[$childModuleName]
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

    $cp = ConstructionPlan::fromConfigFile('exceptionTest.json', $this->moduleList);
  }

  function testThrowsErrorWhenModuleIsNotRegistered() {
    $this->expectException(Exception::class);
    ConstructionPlan::fromConfig([
      'name' => 'ThisModuleIsNotRegistered'
    ], $this->moduleList);
  }

  function testConfigFileLoaderUsesFilterHook() {
    Filters::expectApplied('WPStarter/configFileLoader')
    ->with(null, TestHelper::getConfigPath('exampleConfig.yml'))
    ->once()
    ->andReturn(['name' => 'SingleModule']);

    $cp = ConstructionPlan::fromConfigFile('exampleConfig.yml', $this->moduleList);
  }

  function testModuleWithoutDataIsValid() {
    $moduleName = 'SingleModule';
    $module = TestHelper::getCustomModule($moduleName, ['name', 'areas']);
    $cp = ConstructionPlan::fromConfig($module, $this->moduleList);
    $this->assertEquals($cp, [
      'name' => 'SingleModule',
      'data' => [],
      'path' => $this->moduleList[$moduleName]
    ]);
  }

  function testModuleDataIsFiltered() {
    $moduleName = 'SingleModule';

    // Params: ModuleName, hasFilterArgs = false, returnDuplicate = false
    TestHelper::registerDataFilter($moduleName);

    $module = TestHelper::getCustomModule($moduleName, ['name', 'dataFilter', 'areas']);
    $cp = ConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $moduleName,
      'data' => [
        'test' => 'result'
      ],
      'path' => $this->moduleList[$moduleName]
    ]);
  }

  function testDataFilterArgumentsAreUsed() {
    $moduleName = 'SingleModule';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($moduleName, true, false);

    $module = TestHelper::getCustomModule($moduleName, ['name', 'dataFilter', 'dataFilterArgs', 'areas']);
    $cp = ConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $moduleName,
      'data' => [
        'test' => 'result'
      ],
      'path' => $this->moduleList[$moduleName]
    ]);
  }

  function testCustomDataIsAddedToModule() {
    $moduleName = 'SingleModule';

    // this simulates add_filter with return data:
    $module = TestHelper::getCustomModule($moduleName, ['name', 'customData', 'areas']);
    $cp = ConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $moduleName,
      'data' => [
        'test0' => 0,
        'test1' => 'string',
        'test2' => [
          'something strange'
        ],
        'duplicate' => 'newValue'
      ],
      'path' => $this->moduleList[$moduleName]
    ]);
  }

  function testDataIsFilteredAndCustomDataIsAdded() {
    $moduleName = 'SingleModule';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($moduleName, false, true);

    $module = TestHelper::getCustomModule($moduleName, ['name', 'dataFilter', 'customData', 'areas']);
    $cp = ConstructionPlan::fromConfig($module, $this->moduleList);

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
      ],
      'path' => $this->moduleList[$moduleName]
    ]);
  }

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

    $cp = ConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $parentModuleName,
      'data' => [],
      'path' => $this->moduleList[$parentModuleName],
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
            ],
            'path' => $this->moduleList[$childModuleName]
          ]
        ]
      ]
    ]);
  }

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

    $cp = ConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $parentModuleName,
      'data' => [
        'test' => 'result',
      ],
      'path' => $this->moduleList[$parentModuleName],
      'areas' => [
        'area51' => [
          [
            'name' => $childModuleName,
            'data' => [],
            'path' => $this->moduleList[$childModuleName]
          ]
        ]
      ]
    ]);
  }

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

    $cp = ConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $parentModuleName,
      'data' => [
        'test' => 'result',
      ],
      'path' => $this->moduleList[$parentModuleName],
      'areas' => [
        'area51' => [
          [
            'name' => $childModuleName,
            'data' => [],
            'path' => $this->moduleList[$childModuleName],
            'areas' => [
              'district9' => [
                [
                  'name' => $grandChildModuleNameA,
                  'data' => [],
                  'path' => $this->moduleList[$grandChildModuleNameA]
                ]
              ],
              'alderaan' => [
                [
                  'name' => $grandChildModuleNameB,
                  'data' => [],
                  'path' => $this->moduleList[$grandChildModuleNameB]
                ],
                [
                  'name' => $grandChildModuleNameC,
                  'data' => [],
                  'path' => $this->moduleList[$grandChildModuleNameC]
                ]
              ]
            ]
          ]
        ]
      ]
    ]);
  }

  function testDynamicSubmodulesCanBeAddedWithAFilter() {
    $moduleName = 'ModuleWithArea';
    $dynamicModuleName = 'SingleModule';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($moduleName, false, false);

    $module = TestHelper::getCustomModule($moduleName, ['name', 'dataFilter', 'areas']);
    $dynamicModule = TestHelper::getCustomModule($dynamicModuleName, ['name']);

    Filters::expectApplied("WPStarter/dynamicSubmodules?name={$moduleName}")
    ->with([],  ['test' => 'result'], [])
    ->once()
    ->andReturn(['area51' => [ $dynamicModule ]]);

    $cp = ConstructionPlan::fromConfig($module, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $moduleName,
      'data' => [
        'test' => 'result'
      ],
      'path' => $this->moduleList[$moduleName],
      'areas' => [
        'area51' => [
          [
            'name' => $dynamicModuleName,
            'data' => [],
            'path' => $this->moduleList[$dynamicModuleName]
          ]
        ]
      ]
    ]);
  }

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
    ->with([],  [], ['test' => 'result'])
    ->once()
    ->andReturn(['area51' => [ $dynamicModule ]]);

    $cp = ConstructionPlan::fromConfig($parentModule, $this->moduleList);

    $this->assertEquals($cp, [
      'name' => $parentModuleName,
      'data' => [
        'test' => 'result'
      ],
      'path' => $this->moduleList[$parentModuleName],
      'areas' => [
        'parentArea' => [
          [
            'name' => $childModuleName,
            'data' => [],
            'path' => $this->moduleList[$childModuleName],
            'areas' => [
              'childArea' => [
                [
                  'name' => $childSubmoduleName,
                  'data' => [],
                  'path' => $this->moduleList[$childSubmoduleName],
                  'areas' => [
                    'area51' => [
                      [
                        'name' => $dynamicModuleName,
                        'data' => [],
                        'path' => $this->moduleList[$dynamicModuleName]
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
