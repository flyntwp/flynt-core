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

class ConstructionPlanTest extends TestCase {

  /**
   * @expectedException Exception
   */
  function testEmptyConfig() {
    $cp = ConstructionPlan::fromConfig([]);
  }

  function testSingleModuleNoData() {
    $cp = ConstructionPlan::fromConfig([
      'name' => 'ModuleA',
      'areas' => []
    ]);
    $this->assertEquals($cp, [
      'name' => 'ModuleA',
      'data' => []
    ]);
  }

  function testSingleModuleWithDataFilter() {
    // expect apply_filters to be called with 'WPStarter/DataFilters/A/foo'

    // this simulates add_filter with return data:
    WP_Mock::userFunction('apply_filters_ref_array', [
      'args' => [
        'WPStarter/DataFilters/A/foo', [[]]
      ],
      'times' => 1,
      'return' => [
        'test' => 'result'
      ]
    ]);

    $cp = ConstructionPlan::fromConfig([
      'name' => 'ModuleA',
      'dataFilter' => 'WPStarter/DataFilters/A/foo',
      'areas' => []
    ]);

    $this->assertEquals($cp, [
      'name' => 'ModuleA',
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
        'WPStarter/DataFilters/A/foo',
        [
          [],
          'post'
        ]
      ],
      'times' => 1,
      'return' => [
        'test' => 'result'
      ]
    ]);

    $cp = ConstructionPlan::fromConfig([
      'name' => 'ModuleA',
      'dataFilter' => 'WPStarter/DataFilters/A/foo',
      'dataFilterArgs' => [
        'post'
      ],
      'areas' => []
    ]);

    $this->assertEquals($cp, [
      'name' => 'ModuleA',
      'data' => [
        'test' => 'result'
      ]
    ]);
  }

  function testSingleModuleWithCustomData() {
    // this simulates add_filter with return data:
    $cp = ConstructionPlan::fromConfig([
      'name' => 'ModuleA',
      'customData' => [
        'test0' => 0,
        'test1' => 'string',
        'test2' => [
          'something strange'
        ]
      ],
      'areas' => []
    ]);

    $this->assertEquals($cp, [
      'name' => 'ModuleA',
      'data' => [
        'test0' => 0,
        'test1' => 'string',
        'test2' => [
          'something strange'
        ]
      ]
    ]);
  }

  function testSingleModuleWithDataFilterAndCustomData() {
    // expect apply_filters to be called with 'WPStarter/DataFilters/A/foo'

    // this simulates add_filter with return data:
    WP_Mock::userFunction('apply_filters_ref_array', [
      'args' => [
        'WPStarter/DataFilters/A/foo', [[]]
      ],
      'times' => 1,
      'return' => [
        'test' => 'result',
        'duplicate' => 'previousValue'
      ]
    ]);

    $cp = ConstructionPlan::fromConfig([
      'name' => 'ModuleA',
      'dataFilter' => 'WPStarter/DataFilters/A/foo',
      'customData' => [
        'myData' => 1,
        'otherData' => 'test',
        'duplicate' => 'newValue'
      ],
      'areas' => []
    ]);

    $this->assertEquals($cp, [
      'name' => 'ModuleA',
      'data' => [
        'test' => 'result',
        'myData' => 1,
        'otherData' => 'test',
        'duplicate' => 'newValue'
      ]
    ]);
  }

  //
  // function testNestedModules() {}
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
