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
    $moduleName = 'SingleModule';
    $moduleData = [
      'test' => 'result'
    ];

    TestHelper::registerRenderModuleFilter($moduleData);
    TestHelper::registerRenderSpecificModuleFilter($moduleData, $moduleName);

    $cp = [
      'name' => $moduleName,
      'data' => $moduleData
    ];

    $html = Renderer::fromConstructionPlan($cp);
    $this->assertEquals($html, "<div>{$moduleName} result</div>\n");
  }

  function testNestedModules() {
    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'SingleModule';
    $moduleData = [
      'test' => 'result'
    ];

    TestHelper::registerRenderModuleFilter($moduleData);
    TestHelper::registerRenderSpecificModuleFilter($moduleData, $parentModuleName);
    TestHelper::registerRenderSpecificModuleFilter($moduleData, $childModuleName);

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
    $moduleData = [];
    $cp = [
      'name' => $moduleName,
      'data' => $moduleData
    ];

    $shouldBeHtml = "<div>{$moduleName} After Filter Hook</div>\n";

    TestHelper::registerRenderModuleFilter($moduleData, $shouldBeHtml);
    TestHelper::registerRenderSpecificModuleFilter($moduleData, $moduleName, $shouldBeHtml, $shouldBeHtml);

    $html = Renderer::fromConstructionPlan($cp);
    $this->assertEquals($html, $shouldBeHtml);
  }

  function testCustomHtmlHookSingleModule() {
    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'SingleModule';
    $moduleData = [
      'test' => 'result'
    ];

    // test whether custom html can be used
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

    $shouldBeChildOutput = "<div>{$childModuleName} After Filter Hook</div>\n";
    $shouldBeHtml = "<div>{$parentModuleName} result" . $shouldBeChildOutput . "</div>\n";

    // General Filter renderModule
    TestHelper::registerRenderModuleFilter($moduleData);

    // Specific Filters renderModule?name=SingleModule for example
    TestHelper::registerRenderSpecificModuleFilter($moduleData, $parentModuleName);
    TestHelper::registerRenderSpecificModuleFilter($moduleData, $childModuleName, '', $shouldBeChildOutput);

    $html = Renderer::fromConstructionPlan($cp);
    $this->assertEquals($html, $shouldBeHtml);
  }
}
