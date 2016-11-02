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

  /**
   * @expectedException Exception
   */
  function testEmptyConfig() {
    $cp = ConstructionPlan::fromConfig([]);
  }

  function testSingleModuleNoData() {
    $cp = ConstructionPlan::fromConfig([
      'name' => 'ModuleA'
    ]);
    $this->assertEquals($cp, [
      'name' => 'ModuleA',
      'data' => [],
      'areaHtml' => []
    ]);
  }

  function testSingleModule() {
    // expect apply_filters to be called with 'WPStarter/DataFilters/A/foo'
    $cp = ConstructionPlan::fromConfig([
      'name' => 'ModuleA',
      'dataFilter' => 'WPStarter/DataFilters/A/foo'
    ]);
    $this->assertEquals($cp, [
      'name' => 'ModuleA',
      'data' => [],
      'areaHtml' => []
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
