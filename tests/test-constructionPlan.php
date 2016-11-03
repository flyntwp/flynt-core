<?php
/**
 * Class ConstructionPlanTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Construction plan test case.
 */

use PHPUnit\Framework\TestCase;
use WPStarter\ConstructionPlan;

class ConstructionPlanTest extends TestCase {

  function setUp() {
    WP_Mock::setUp();
  }

  function tearDown() {
    WP_Mock::setUp();
  }

  /**
   * @expectedException Exception
   */
  function testEmptyConfig() {
    $cp = ConstructionPlan::fromConfig([]);
  }

  function testSingleModuleNoData() {
    $module = TestHelper::getCustomModule('ModuleNoData', ['name', 'areas']);
    $cp = ConstructionPlan::fromConfig($module);
    $this->assertEquals($cp, [
      'name' => 'ModuleNoData',
      'data' => []
    ]);
  }

  function testSingleModuleWithDataFilter() {
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

  function testSingleModuleWithDataFilterArgs() {
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

  function testSingleModuleWithCustomData() {
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

  function testSingleModuleWithDataFilterAndCustomData() {
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

  function testNestedModules() {
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

  function testNestedModulesWithParentDataOnly() {
    $parentModuleName = 'ModuleNestedParent';
    $childModuleName = 'ModuleNestedChild';

    // Params: ModuleName, hasFilterArgs, returnDuplicate
    TestHelper::registerDataFilter($parentModuleName, false, false);

    $module = TestHelper::getCustomModule($parentModuleName, ['name', 'dataFilter', 'areas']);

    $module['areas'] = [
      'Area51' => [
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
        'Area51' => [
          [
            'name' => $childModuleName,
            'data' => []
          ]
        ]
      ]
    ]);
  }

  //
  // function testDeeplyNestedModules() {}
  //
  // function testDynamicSubmodules() {}
  //
  // function testObjectAsArgument() {
  //   $cp = ConstructionPlan::fromConfig(new StdClass());
  //   $this->assertErrorThrown();
  // }

}
