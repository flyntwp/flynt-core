<?php
/**
 * Class BuildConstructionPlanTest
 *
 * @package Flynt_Core
 */

/**
 * Build Construction Plan test case.
 */

namespace Flynt\Tests;

require_once dirname(__DIR__) . '/lib/Flynt/BuildConstructionPlan.php';

use StdClass;
use Mockery;
use Brain\Monkey\WP\Actions;
use Brain\Monkey\WP\Filters;
use Flynt\Tests\TestCase;
use Flynt\BuildConstructionPlan;

class BuildConstructionPlanTest extends TestCase
{

    public function setUp()
    {
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

    public function badValues()
    {
        return [
        [[]],
        [[
        'customData' => [
          'whatever'
        ]
        ]],
        [new StdClass()],
        ['string'],
        [0]
        ];
    }

    public function badValuesComponentManager()
    {
        return [
        [[
        'name' => 'ThisComponentIsNotRegistered'
        ]],
      [[
        'name' => ''
      ]],
        [[
        'name' => []
        ]]
        ];
    }

  /**
   * @dataProvider badValues
   */
    public function testShowWarningOnInvalidConfig($badValue)
    {
        $this->expectException('PHPUnit_Framework_Error_Warning');
        $cp = BuildConstructionPlan::fromConfig($badValue);
    }

  /**
   * @dataProvider badValues
   */
    public function testReturnsEmptyConstructionPlanOnInvalidConfig($badValue)
    {
        $cp = @BuildConstructionPlan::fromConfig($badValue);
        $this->assertEquals($cp, []);
    }

  /**
   * @dataProvider badValuesComponentManager
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
    public function testShowWarningOnInvalidConfigWithComponentManager($badValue)
    {
        $this->expectException('PHPUnit_Framework_Error_Warning');
        $this->mockComponentManager();
        BuildConstructionPlan::fromConfig($badValue);
    }

  /**
   * @dataProvider badValuesComponentManager
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
    public function testReturnsEmptyConstructionPlanOnInvalidConfigWithComponentManager($badValue)
    {
        $this->mockComponentManager();
        $cp = @BuildConstructionPlan::fromConfig($badValue);
        $this->assertEquals($cp, []);
    }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
    public function testInvalidCustomDataIsIgnored()
    {
        $config = [
        'name' => 'SingleComponent',
        'customData' => 'string'
        ];
        $this->mockComponentManager();
        $cp = BuildConstructionPlan::fromConfig($config, $this->componentList);
        $this->assertEquals($cp, [
        'name' => 'SingleComponent',
        'data' => []
        ]);
    }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
    public function testInvalidParentDataIsIgnored()
    {
        $parentData = 'string';
        $config = [
        'name' => 'SingleComponent',
        'parentData' => $parentData
        ];
        $this->mockComponentManager();
        Filters::expectApplied('Flynt/addComponentData')
        ->with([], [], [
        'name' => 'SingleComponent',
        'data' => []
        ])
        ->once()
        ->andReturn([]);
        $cp = BuildConstructionPlan::fromConfig($config, $this->componentList);
        $this->assertEquals($cp, [
        'name' => 'SingleComponent',
        'data' => []
        ]);
    }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
    public function testConfigCanBeLoadedFromFile()
    {
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

    public function testShowWarningWhenConfigFileDoesntExist()
    {
        $fileName = 'exceptionTest.json';

        Filters::expectApplied('Flynt/configPath')
        ->once()
        ->with(null, $fileName)
        ->andReturn('/not/a/real/config/file.json');

        $this->expectException('PHPUnit_Framework_Error_Warning');

        $cp = BuildConstructionPlan::fromConfigFile($fileName);
    }

    public function testReturnsEmptyConstructionPlanWhenConfigFileDoesntExist()
    {
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
    public function testComponentWithoutDataIsValid()
    {
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
    public function testCustomDataIsAddedToComponent()
    {
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
    public function testaddComponentDataFiltersAreApplied()
    {
        // Made this more complex than necessary to also test parentData being passed
        $parentComponentName = 'ComponentWithArea';
        $childComponentName = 'SingleComponent';

        $parentComponent = TestHelper::getCustomComponent($parentComponentName, ['name', 'areas']);
        $childComponent = TestHelper::getCompleteComponent($childComponentName);

        $parentComponent['areas'] = [
        'Area51' => [
        $childComponent
        ]
        ];

        $this->mockComponentManager();

        $parentData = [];

        $childData = [
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
        unset($parentComponentAsArg['customData']);

        $childComponentAsArg = array_merge($childComponent, [
        'data' => $childData
        ]);
        unset($childComponentAsArg['customData']);

        Filters::expectApplied('Flynt/addComponentData')
        ->with($parentData, [], $parentComponentAsArg)
        ->ordered()
        ->once()
        ->andReturn($parentData);

        Filters::expectApplied('Flynt/addComponentData')
        ->with($childData, $parentData, $childComponentAsArg)
        ->ordered()
        ->once()
        ->andReturn($childData);

        Filters::expectApplied("Flynt/addComponentData?name={$childComponentName}")
        ->with($childData, $parentData, $childComponentAsArg)
        ->once()
        ->andReturn($newChildData);

        $cp = BuildConstructionPlan::fromConfig($parentComponent, $this->componentList);

        $this->assertEquals($cp, [
        'name' => $parentComponentName,
        'data' => [],
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
    public function testNestedComponentIsAddedToArea()
    {
        $parentComponentName = 'ComponentWithArea';
        $childComponentName = 'SingleComponent';

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
    public function testParentComponentDataIsNotAddedToChildComponent()
    {
        $parentComponentName = 'ComponentWithArea';
        $childComponentName = 'SingleComponent';

        $component = TestHelper::getCustomComponent($parentComponentName, ['name', 'areas']);
        $component['customData'] = [
        'testParentData' => true
        ];

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
        'testParentData' => true
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
    public function testParentDataIsOverwritten()
    {
        $parentComponentName = 'ComponentWithArea';
        $childComponentName = 'SingleComponent';

        $newParentData = [
        'custom' => 'parentData'
        ];

        $component = TestHelper::getCustomComponent($parentComponentName, ['name', 'areas']);
        $childComponent = TestHelper::getCustomComponent($childComponentName, ['name']);
        $childComponent['parentData'] = $newParentData;

        $component['customData'] = [
        'testParentData' => true
        ];

        $component['areas'] = [
        'area51' => [
        $childComponent
        ]
        ];

        $this->mockComponentManager();

        Filters::expectApplied('Flynt/addComponentData')
        ->with(['testParentData' => true], [], Mockery::type('array'))
        ->ordered()
        ->once()
        ->andReturn(['testParentData' => true]);

        Filters::expectApplied('Flynt/addComponentData')
        ->with([], $newParentData, Mockery::type('array'))
        ->ordered()
        ->once()
        ->andReturn($newParentData);

        $cp = BuildConstructionPlan::fromConfig($component, $this->componentList);

        $this->assertEquals($cp, [
        'name' => $parentComponentName,
        'data' => [
        'testParentData' => true
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
    public function testDeeplyNestedComponentsCreateValidConstructionPlan()
    {
        $parentComponentName = 'ComponentWithArea';
        $childComponentName = 'NestedComponentWithArea';
        $grandChildComponentNameA = 'GrandChildA';
        $grandChildComponentNameB = 'GrandChildB';
        $grandChildComponentNameC = 'GrandChildC';

        $component = TestHelper::getCustomComponent($parentComponentName, ['name', 'areas']);

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
        'data' => [],
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
    public function testDynamicSubcomponentsCanBeAddedWithAFilter()
    {
        $componentName = 'ComponentWithArea';
        $dynamicComponentName = 'SingleComponent';

        $component = TestHelper::getCustomComponent($componentName, ['name', 'areas']);
        $dynamicComponent = TestHelper::getCustomComponent($dynamicComponentName, ['name']);

        Filters::expectApplied("Flynt/dynamicSubcomponents?name={$componentName}")
        ->with([], [], [])
        ->once()
        ->andReturn(['area51' => [ $dynamicComponent ]]);

        $this->mockComponentManager();

        $cp = BuildConstructionPlan::fromConfig($component, $this->componentList);

        $this->assertEquals($cp, [
        'name' => $componentName,
        'data' => [],
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
    public function testDynamicSubcomponentsReceiveParentData()
    {
        $parentComponentName = 'ComponentWithArea';
        $childComponentName = 'NestedComponentWithArea';
        $childSubcomponentName = 'SingleComponent';
        $dynamicComponentName = 'DynamicComponent';

        $parentComponent = TestHelper::getCustomComponent($parentComponentName, ['name', 'areas']);
        $childComponent = TestHelper::getCustomComponent($childComponentName, ['name']);
        $childSubcomponent = TestHelper::getCustomComponent($childSubcomponentName, ['name']);
        $dynamicComponent = TestHelper::getCustomComponent($dynamicComponentName, ['name']);

        $childComponent['areas'] = [
        'childArea' => [ $childSubcomponent ]
        ];
        $parentComponent['areas'] = [
        'parentArea' => [ $childComponent ]
        ];

        $parentComponent['customData'] = [
        'testParentData' => true
        ];

        Filters::expectApplied("Flynt/dynamicSubcomponents?name={$childSubcomponentName}")
        ->with([], [], ['testParentData' => true])
        ->once()
        ->andReturn(['area51' => [ $dynamicComponent ]]);

        $this->mockComponentManager();

        $cp = BuildConstructionPlan::fromConfig($parentComponent, $this->componentList);

        $this->assertEquals($cp, [
        'name' => $parentComponentName,
        'data' => [
        'testParentData' => true
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
    public function testInvalidComponentsInAreasAreRemoved()
    {
        $parentComponentName = 'ComponentWithArea';
        $childComponentName = 'DoesNotExist';

        $component = TestHelper::getCustomComponent($parentComponentName, ['name', 'areas']);

        $component['areas'] = [
        'area51' => [
        TestHelper::getCustomComponent($childComponentName, ['name'])
        ]
        ];

        $this->mockComponentManager();

        $cp = @BuildConstructionPlan::fromConfig($component, $this->componentList);
        $this->assertEquals($cp, [
        'name' => $parentComponentName,
        'data' => [],
        'areas' => [
        'area51' => []
        ]
        ]);
    }

    public function testDoesBeforeAndAfterActions()
    {
        $config = [
            'name' => 'test'
        ];

        $expectedConstructionPlan = [];

        Actions::expectFired('Flynt/beforeBuildConstructionPlan')
        ->once()
        ->with($config);

        Actions::expectFired('Flynt/afterBuildConstructionPlan')
        ->once()
        ->with($expectedConstructionPlan);

        $cp = @BuildConstructionPlan::fromConfig($config);
    }

  // Helpers
    public function mockComponentManager()
    {
        $componentManagerMock = Mockery::mock('ComponentManager');

        Mockery::mock('alias:Flynt\ComponentManager')
        ->shouldReceive('getInstance')
        ->andReturn($componentManagerMock);

        $componentManagerMock
        ->shouldReceive('isRegistered')
        ->andReturnUsing([$this, 'componentIsInList']);
    }

    public function componentIsInList($componentName)
    {
        return array_key_exists($componentName, $this->componentList);
    }
}
