<?php
/**
 * Class ConstructionPlanTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Construction plan test case.
 */

use WPStarter\ConstructionPlan;

class ConstructionPlanTest extends WP_UnitTestCase {

  /**
   * A single example test.
   */
  function testEmptyConfig() {
    $cp = ConstructionPlan::fromConfig([]);
    $this->assertEquals($cp->toArray(), []);
  }

  function testSingleModule() {}

  function testNestedModules() {}

  function testDeeplyNestedModules() {}

  function testDynamicSubmodules() {}

  function testObjectAsArgument() {
    $cp = ConstructionPlan::fromConfig(new StdClass());
    $this->assertErrorThrown();
  }

}
