<?php
/**
 * Class BuildConstructionPlanTest
 *
 * @package Flynt_Core
 */

/**
 * Build Construction Plan test case.
 */

require_once dirname(__DIR__) . '/lib/Flynt/BuildConstructionPlan.php';

use Flynt\TestCase;
use Flynt\BuildConstructionPlan;
use Brain\Monkey\WP\Filters;

class BuildConstructionPlanTest extends TestCase {

  function setUp() {
    parent::setUp();

    $this->componentList = [
      'DynamicComponent' => '',
      'SingleComponent' => '',
      'ComponentWithArea' => '',
      'NestedComponentWithArea' => '',
      'ComponentInConfigFile' => '',
      'ChildComponentInConfigFile' => '',
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

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testShowWarningWhenComponentIsNotRegistered() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    $this->mockComponentManager();
    BuildConstructionPlan::fromConfig([
      'name' => 'ThisComponentIsNotRegistered'
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testReturnsEmptyConstructionPlanWhenComponentIsNotRegistered() {
    $this->mockComponentManager();
    $cp = @BuildConstructionPlan::fromConfig([
      'name' => 'ThisComponentIsNotRegistered'
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

    Filters::expectApplied('Flynt/configPath')
    ->andReturn($filePath);

    Filters::expectApplied('Flynt/configFileLoader')
    ->once()
    ->with(null, $fileName, $filePath)
    ->andReturn([
      'name' => 'SingleComponent'
    ]);

    $this->mockComponentManager();

    $cp = BuildConstructionPlan::fromConfigFile($fileName);
    $this->assertEquals($cp, [
      'name' => 'SingleComponent',
      'data' => []
    ]);
  }

  function testShowWarningWhenConfigFileDoesntExist() {
    $fileName = 'exceptionTest.json';

    Filters::expectApplied('Flynt/configPath')
    ->once()
    ->with(null, $fileName)
    ->andReturn('/not/a/real/config/file.json');

    $this->expectException('PHPUnit_Framework_Error_Warning');

    $cp = BuildConstructionPlan::fromConfigFile($fileName);
  }

  function testReturnsEmptyConstructionPlanWhenConfigFileDoesntExist() {
    $fileName = 'exceptionTest.json';

    Filters::expectApplied('Flynt/configPath')
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
  function testComponentWithoutDataIsValid() {
    $component = TestHelper::getCustomComponent('SingleComponent', ['name', 'areas']);

    $this->mockComponentManager();

    $cp = BuildConstructionPlan::fromConfig($component, $this->componentList);

    $this->assertEquals($cp, [
      'name' => 'SingleComponent',
      'data' => []
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testComponentDataIsFiltered() {
    $componentName = 'SingleComponent';

    // Params: ComponentName, hasFilterArgs = false, returnDuplicate = false
    TestHelper::registerDataFilter($componentName);

    $component = TestHelper::getCustomComponent($componentName, ['name', 'dataFilter', 'areas']);

    $this->mockComponentManager();

    $cp = BuildConstructionPlan::fromConfig($component, $this->componentList);

    $this->assertEquals($cp, [
      'name' => $componentName,
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
    $componentName = 'SingleComponent';

    // Params: ComponentName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($componentName, true, false);

    $component = TestHelper::getCustomComponent($componentName, ['name', 'dataFilter', 'dataFilterArgs', 'areas']);

    $this->mockComponentManager();

    $cp = BuildConstructionPlan::fromConfig($component, $this->componentList);

    $this->assertEquals($cp, [
      'name' => $componentName,
      'data' => [
        'test' => 'result'
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testCustomDataIsAddedToComponent() {
    $componentName = 'SingleComponent';

    // this simulates add_filter with return data:
    $component = TestHelper::getCustomComponent($componentName, ['name', 'customData', 'areas']);

    $this->mockComponentManager();

    $cp = BuildConstructionPlan::fromConfig($component, $this->componentList);

    $this->assertEquals($cp, [
      'name' => $componentName,
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
    $componentName = 'SingleComponent';

    // Params: ComponentName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($componentName, false, true);

    $component = TestHelper::getCustomComponent($componentName, ['name', 'dataFilter', 'customData', 'areas']);

    $this->mockComponentManager();

    $cp = BuildConstructionPlan::fromConfig($component, $this->componentList);

    $this->assertEquals($cp, [
      'name' => $componentName,
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
  function testModifyComponentDataFiltersAreApplied() {
    // Made this more complex than necessary to also test parentData being passed
    $parentComponentName = 'ComponentWithArea';
    $childComponentName = 'SingleComponent';

    // Params: ComponentName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($parentComponentName);

    // Params: ComponentName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($childComponentName, true, true);

    $parentComponent = TestHelper::getCustomComponent($parentComponentName, ['name', 'dataFilter', 'areas']);
    $childComponent = TestHelper::getCompleteComponent($childComponentName);

    $parentComponent['areas'] = [
      'Area51' => [
        $childComponent
      ]
    ];

    $this->mockComponentManager();

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

    $parentComponentAsArg = array_merge($parentComponent, [
      'data' => $parentData
    ]);
    unset($parentComponentAsArg['dataFilter']);
    unset($parentComponentAsArg['dataFilterArgs']);
    unset($parentComponentAsArg['customData']);

    $childComponentAsArg = array_merge($childComponent, [
      'data' => $childData
    ]);
    unset($childComponentAsArg['dataFilter']);
    unset($childComponentAsArg['dataFilterArgs']);
    unset($childComponentAsArg['customData']);

    Filters::expectApplied('Flynt/modifyComponentData')
    ->with($parentData, [], $parentComponentAsArg)
    ->ordered()
    ->once()
    ->andReturn($parentData);

    Filters::expectApplied('Flynt/modifyComponentData')
    ->with($childData, $parentData, $childComponentAsArg)
    ->ordered()
    ->once()
    ->andReturn($childData);

    Filters::expectApplied("Flynt/modifyComponentData?name={$childComponentName}")
    ->with($childData, $parentData, $childComponentAsArg)
    ->once()
    ->andReturn($newChildData);

    $cp = BuildConstructionPlan::fromConfig($parentComponent, $this->componentList);

    $this->assertEquals($cp, [
      'name' => $parentComponentName,
      'data' => [
        'test' => 'result'
      ],
      'areas' => [
        'Area51' => [
          [
            'name' => $childComponentName,
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
  function testNestedComponentIsAddedToArea() {
    $parentComponentName = 'ComponentWithArea';
    $childComponentName = 'SingleComponent';

    // Params: ComponentName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($childComponentName, true, true);

    $component = TestHelper::getCustomComponent($parentComponentName, ['name', 'areas']);

    $component['areas'] = [
      'Area51' => [
        TestHelper::getCompleteComponent($childComponentName)
      ]
    ];

    $this->mockComponentManager();

    $cp = BuildConstructionPlan::fromConfig($component, $this->componentList);

    $this->assertEquals($cp, [
      'name' => $parentComponentName,
      'data' => [],
      'areas' => [
        'Area51' => [
          [
            'name' => $childComponentName,
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
  function testParentComponentDataIsNotAddedToChildComponent() {
    $parentComponentName = 'ComponentWithArea';
    $childComponentName = 'SingleComponent';

    // Params: ComponentName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($parentComponentName, false, false);

    $component = TestHelper::getCustomComponent($parentComponentName, ['name', 'dataFilter', 'areas']);

    $component['areas'] = [
      'area51' => [
        TestHelper::getCustomComponent($childComponentName, ['name'])
      ]
    ];

    $this->mockComponentManager();

    $cp = BuildConstructionPlan::fromConfig($component, $this->componentList);

    $this->assertEquals($cp, [
      'name' => $parentComponentName,
      'data' => [
        'test' => 'result',
      ],
      'areas' => [
        'area51' => [
          [
            'name' => $childComponentName,
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
    $parentComponentName = 'ComponentWithArea';
    $childComponentName = 'SingleComponent';

    // Params: ComponentName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($parentComponentName, false, false);

    $newParentData = [
      'custom' => 'parentData'
    ];

    $component = TestHelper::getCustomComponent($parentComponentName, ['name', 'dataFilter', 'areas']);
    $childComponent = TestHelper::getCustomComponent($childComponentName, ['name']);
    $childComponent['parentData'] = $newParentData;

    $component['areas'] = [
      'area51' => [
        $childComponent
      ]
    ];

    $this->mockComponentManager();

    Filters::expectApplied('Flynt/modifyComponentData')
    ->with(['test' => 'result'], [], Mockery::type('array'))
    ->ordered()
    ->once()
    ->andReturn(['test' => 'result']);

    Filters::expectApplied('Flynt/modifyComponentData')
    ->with([], $newParentData, Mockery::type('array'))
    ->ordered()
    ->once()
    ->andReturn($newParentData);

    $cp = BuildConstructionPlan::fromConfig($component, $this->componentList);

    $this->assertEquals($cp, [
      'name' => $parentComponentName,
      'data' => [
        'test' => 'result',
      ],
      'areas' => [
        'area51' => [
          [
            'name' => $childComponentName,
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
  function testDeeplyNestedComponentsCreateValidConstructionPlan() {
    $parentComponentName = 'ComponentWithArea';
    $childComponentName = 'NestedComponentWithArea';
    $grandChildComponentNameA = 'GrandChildA';
    $grandChildComponentNameB = 'GrandChildB';
    $grandChildComponentNameC = 'GrandChildC';

    // Params: ComponentName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($parentComponentName, false, false);

    $component = TestHelper::getCustomComponent($parentComponentName, ['name', 'dataFilter', 'areas']);

    $component['areas'] = [
      'area51' => [
        TestHelper::getCustomComponent($childComponentName, ['name', 'areas'])
      ]
    ];

    $component['areas']['area51'][0]['areas'] = [
      'district9' => [
        TestHelper::getCustomComponent($grandChildComponentNameA, ['name'])
      ],
      'alderaan' => [
        TestHelper::getCustomComponent($grandChildComponentNameB, ['name']),
        TestHelper::getCustomComponent($grandChildComponentNameC, ['name'])
      ]
    ];

    $this->mockComponentManager();

    $cp = BuildConstructionPlan::fromConfig($component, $this->componentList);

    $this->assertEquals($cp, [
      'name' => $parentComponentName,
      'data' => [
        'test' => 'result',
      ],
      'areas' => [
        'area51' => [
          [
            'name' => $childComponentName,
            'data' => [],
            'areas' => [
              'district9' => [
                [
                  'name' => $grandChildComponentNameA,
                  'data' => []
                ]
              ],
              'alderaan' => [
                [
                  'name' => $grandChildComponentNameB,
                  'data' => []
                ],
                [
                  'name' => $grandChildComponentNameC,
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
  function testDynamicSubcomponentsCanBeAddedWithAFilter() {
    $componentName = 'ComponentWithArea';
    $dynamicComponentName = 'SingleComponent';

    // Params: ComponentName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($componentName, false, false);

    $component = TestHelper::getCustomComponent($componentName, ['name', 'dataFilter', 'areas']);
    $dynamicComponent = TestHelper::getCustomComponent($dynamicComponentName, ['name']);

    Filters::expectApplied("Flynt/dynamicSubcomponents?name={$componentName}")
    ->with([], ['test' => 'result'], [])
    ->once()
    ->andReturn(['area51' => [ $dynamicComponent ]]);

    $this->mockComponentManager();

    $cp = BuildConstructionPlan::fromConfig($component, $this->componentList);

    $this->assertEquals($cp, [
      'name' => $componentName,
      'data' => [
        'test' => 'result'
      ],
      'areas' => [
        'area51' => [
          [
            'name' => $dynamicComponentName,
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
  function testDynamicSubcomponentsReceiveParentData() {
    $parentComponentName = 'ComponentWithArea';
    $childComponentName = 'NestedComponentWithArea';
    $childSubcomponentName = 'SingleComponent';
    $dynamicComponentName = 'DynamicComponent';

    // Params: ComponentName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($parentComponentName, false, false);

    $parentComponent = TestHelper::getCustomComponent($parentComponentName, ['name', 'dataFilter', 'areas']);
    $childComponent = TestHelper::getCustomComponent($childComponentName, ['name']);
    $childSubcomponent = TestHelper::getCustomComponent($childSubcomponentName, ['name']);
    $dynamicComponent = TestHelper::getCustomComponent($dynamicComponentName, ['name']);

    $childComponent['areas'] = [
      'childArea' => [ $childSubcomponent ]
    ];
    $parentComponent['areas'] = [
      'parentArea' => [ $childComponent ]
    ];

    Filters::expectApplied("Flynt/dynamicSubcomponents?name={$childSubcomponentName}")
    ->with([], [], ['test' => 'result'])
    ->once()
    ->andReturn(['area51' => [ $dynamicComponent ]]);

    $this->mockComponentManager();

    $cp = BuildConstructionPlan::fromConfig($parentComponent, $this->componentList);

    $this->assertEquals($cp, [
      'name' => $parentComponentName,
      'data' => [
        'test' => 'result'
      ],
      'areas' => [
        'parentArea' => [
          [
            'name' => $childComponentName,
            'data' => [],
            'areas' => [
              'childArea' => [
                [
                  'name' => $childSubcomponentName,
                  'data' => [],
                  'areas' => [
                    'area51' => [
                      [
                        'name' => $dynamicComponentName,
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
  function testAppliesInitComponentConfigFilters() {
    $componentName = 'ComponentWithArea';
    $childComponentName = 'SingleComponent';

    $componentData = ['test' => 'result'];

    $componentConfig = TestHelper::getCustomComponent($componentName, ['name', 'areas']);
    $childComponentConfig = TestHelper::getCustomComponent($childComponentName, ['name']);

    $componentConfig['areas']['area51'][0] = $childComponentConfig;

    $componentConfigFilterParam = $componentConfig;
    $childComponentConfigFilterParam = $childComponentConfig;

    $componentConfigFilterParam['data'] = [];
    $childComponentConfigFilterParam['data'] = [];

    $componentConfigAfterInit = array_merge($componentConfigFilterParam, ['data' => $componentData]);
    $childComponentConfigAfterInit = array_merge($childComponentConfigFilterParam, ['data' => $componentData]);

    Filters::expectApplied('Flynt/initComponentConfig')
    ->with($componentConfigFilterParam, null, [])
    ->ordered()
    ->once()
    ->andReturn($componentConfigAfterInit);

    Filters::expectApplied('Flynt/initComponentConfig')
    ->with($childComponentConfigFilterParam, 'area51', $componentData)
    ->ordered()
    ->once()
    ->andReturn($childComponentConfigFilterParam);

    Filters::expectApplied("Flynt/initComponentConfig?name={$componentName}")
    ->with($componentConfigAfterInit, null, [])
    ->once()
    ->andReturn($componentConfigAfterInit);

    Filters::expectApplied("Flynt/initComponentConfig?name={$childComponentName}")
    ->with($childComponentConfigFilterParam, 'area51', $componentData)
    ->once()
    ->andReturn($childComponentConfigAfterInit);

    $this->mockComponentManager();

    BuildConstructionPlan::fromConfig($componentConfig);
  }

  // Helpers
  function mockComponentManager() {
    $componentManagerMock = Mockery::mock('ComponentManager');

    Mockery::mock('alias:Flynt\ComponentManager')
    ->shouldReceive('getInstance')
    ->andReturn($componentManagerMock);

    $componentManagerMock
    ->shouldReceive('isRegistered')
    ->andReturnUsing([$this, 'componentIsInList']);
  }

  function componentIsInList($componentName) {
    return array_key_exists($componentName, $this->componentList);
  }
}
