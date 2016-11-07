<?php
/**
 * Class ConstructionPlanTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Construction plan test case.
 */

use WPStarter\TestCase;
use WPStarter\Renderer;
use Brain\Monkey\WP\Filters;

class RendererTest extends TestCase {
  function setUp() {
    parent::setUp();
    Filters::expectApplied('WPStarter/defaultModulesPath')
    ->with('')
    ->andReturn(__DIR__ . '/assets/src/');
  }

  function testThrowsErrorWhenConstructionPlanIsEmpty() {
    $this->expectException(Exception::class);
    $cp = Renderer::fromConstructionPlan([]);
  }

  function testThrowsErrorWhenModuleFileDoesntExist() {
    Filters::expectApplied('WPStarter/defaultModulesPath')
    ->andReturn('');

    $this->expectException(Exception::class);

    $cp = Renderer::fromConstructionPlan([
      'name' => 'Module',
      'data' => []
    ]);
  }

  function testRendersSingleModuleCorrectly() {
    $moduleName = 'SingleModule';
    $moduleData = [
      'test' => 'result'
    ];

    $cp = [
      'name' => $moduleName,
      'data' => $moduleData
    ];

    $html = Renderer::fromConstructionPlan($cp);
    $this->assertEquals($html, "<div>{$moduleName} result</div>\n");
  }

  function testRendersNestedModulesCorrectly() {
    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'SingleModule';
    $moduleData = [
      'test' => 'result'
    ];

    // check if filter ran exactly twice
    Filters::expectApplied('WPStarter/Renderer/renderModule')
    ->times(2);

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

  function testAppliesCustomHtmlHook() {
    // disable template path, which we don't use here
    Filters::expectApplied('WPStarter/defaultModulesPath')
    ->andReturn('');

    // test whether custom html can be used
    $moduleName = 'SingleModule';
    $moduleData = [];
    $cp = [
      'name' => $moduleName,
      'data' => $moduleData
    ];

    $shouldBeHtml = "<div>{$moduleName} After Filter Hook</div>\n";

    Filters::expectApplied('WPStarter/Renderer/renderModule')
    ->once()
    ->with('', $moduleData)
    ->andReturn($shouldBeHtml);

    $html = Renderer::fromConstructionPlan($cp);
    $this->assertEquals($html, $shouldBeHtml);
  }

  function testAppliesCustomHtmlHookToANestedModule() {
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

    // Specific Filters renderModule?name=SingleModule for example
    Filters::expectApplied('WPStarter/Renderer/renderModule?name=' . $childModuleName)
    ->once()
    ->with('', $moduleData)
    ->andReturn($shouldBeChildOutput);

    $html = Renderer::fromConstructionPlan($cp);
    $this->assertEquals($html, $shouldBeHtml);
  }
}
