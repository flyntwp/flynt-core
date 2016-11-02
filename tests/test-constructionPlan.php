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
use WP_Mock;

require_once 'TestHelper.php';

class ConstructionPlanTest extends TestCase {

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
    // expect apply_filters to be called with 'WPStarter/DataFilters/A/foo'

    // this simulates add_filter with return data:
    WP_Mock::userFunction('apply_filters_ref_array', [
      'args' => [
        'WPStarter/DataFilters/ModuleWithDataFilter/foo', [[]]
      ],
      'times' => 1,
      'return' => [
        'test' => 'result'
      ]
    ]);

    $module = TestHelper::getCustomModule('ModuleWithDataFilter', ['name', 'dataFilter', 'areas']);
    $cp = ConstructionPlan::fromConfig($module);

    $this->assertEquals($cp, [
      'name' => 'ModuleWithDataFilter',
      'data' => [
        'test' => 'result'
      ]
    ]);
  }

  function testSingleModuleWithDataFilterArgs() {
    // expect apply_filters to be called with 'WPStarter/DataFilters/A/foo'

    // this simulates add_filter with return data:
    WP_Mock::userFunction('apply_filters_ref_array', [
      'args' => [
        'WPStarter/DataFilters/ModuleWithDataFilterArgs/foo',
        [
          [],
          'post',
          'page'
        ]
      ],
      'times' => 1,
      'return' => [
        'test' => 'result'
      ]
    ]);

    $module = TestHelper::getCustomModule('ModuleWithDataFilterArgs', ['name', 'dataFilter', 'dataFilterArgs', 'areas']);
    $cp = ConstructionPlan::fromConfig($module);

    $this->assertEquals($cp, [
      'name' => 'ModuleWithDataFilterArgs',
      'data' => [
        'test' => 'result'
      ]
    ]);
  }

  function testSingleModuleWithCustomData() {
    // this simulates add_filter with return data:
    $module = TestHelper::getCustomModule('ModuleWithCustomData', ['name', 'customData', 'areas']);
    $cp = ConstructionPlan::fromConfig($module);

    $this->assertEquals($cp, [
      'name' => 'ModuleWithCustomData',
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
    // expect apply_filters to be called with 'WPStarter/DataFilters/A/foo'

    // this simulates add_filter with return data:
    WP_Mock::userFunction('apply_filters_ref_array', [
      'args' => [
        'WPStarter/DataFilters/ModuleWithDataFilterAndCustomData/foo', [[]]
      ],
      'times' => 1,
      'return' => [
        'test' => 'result',
        'duplicate' => 'previousValue'
      ]
    ]);

    $module = TestHelper::getCustomModule('ModuleWithDataFilterAndCustomData', ['name', 'dataFilter', 'customData', 'areas']);
    $cp = ConstructionPlan::fromConfig($module);

    $this->assertEquals($cp, [
      'name' => 'ModuleWithDataFilterAndCustomData',
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
    WP_Mock::userFunction('apply_filters_ref_array', [
      'args' => [
        'WPStarter/DataFilters/ModuleNestedChild/foo', [[], 'post', 'page']
      ],
      'times' => 1,
      'return' => [
        'test' => 'result',
        'duplicate' => 'previousValue'
      ]
    ]);

    $module = TestHelper::getCustomModule('ModuleNestedParent', ['name', 'areas']);

    $module['areas'] = [
      'Area51' => [
        TestHelper::getCompleteModule('ModuleNestedChild')
      ]
    ];

    $cp = ConstructionPlan::fromConfig($module);

    $this->assertEquals($cp, [
      'name' => 'ModuleNestedParent',
      'data' => [],
      'areas' => [
        'Area51' => [
          [
            'name' => 'ModuleNestedChild',
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

  // TODO write test with parent data

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
