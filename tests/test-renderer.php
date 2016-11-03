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
use WPStarter\Renderer;

class RendererTest extends TestCase {
  function setUp() {
    WP_Mock::setUp();

    WP_Mock::onFilter('WPStarter/defaultModulesPath')
    ->with('')
    ->reply(__DIR__ . '/assets/src/');
  }

  function tearDown() {
    WP_Mock::tearDown();
  }

  /**
   * @expectedException Exception
   */
  function testEmptyConstructionPlan() {
    $cp = Renderer::fromConstructionPlan([]);
  }

  function testSingleModule() {
    $moduleData = [
      'test' => 'result'
    ];

    TestHelper::registerRenderModuleFilter($moduleData);

    $moduleName = 'SingleModule';
    $cp = [
      'name' => $moduleName,
      'data' => $moduleData
    ];

    $html = Renderer::fromConstructionPlan($cp);
    $this->assertEquals($html, "<div>{$moduleName} result</div>\n");
  }

  function testNestedModules() {
    $moduleData = [
      'test' => 'result'
    ];

    TestHelper::registerRenderModuleFilter($moduleData);

    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'SingleModule';
    $cp = [
      'name' => $parentModuleName,
      'data' => $moduleData,
      'areas' => [
        'area51' => [
          [
            'name' => $childModuleName,
            'data' => $moduleData
          ]
        ]
      ]
    ];

    $html = Renderer::fromConstructionPlan($cp);

    $this->assertEquals($html, "<div>{$parentModuleName} result<div>{$childModuleName} result</div>\n</div>\n");
  }

  function testCustomHtmlHook() {
    // test whether custom html can be used
    $moduleName = 'SingleModule';
    $cp = [
      'name' => $moduleName,
      'data' => []
    ];

    $shouldBeHtml = "<div>{$moduleName} After Filter Hook</div>\n";

    TestHelper::registerRenderModuleFilter($cp['data'], $shouldBeHtml);

    $html = Renderer::fromConstructionPlan($cp);
    $this->assertEquals($html, $shouldBeHtml);
  }
}
