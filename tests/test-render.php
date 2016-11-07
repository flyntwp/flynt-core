<?php
/**
 * Class ConstructionPlanTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Construction plan test case.
 */

require_once dirname(__DIR__) . '/lib/WPStarter/Render.php';

use WPStarter\TestCase;
use WPStarter\Render;
use Brain\Monkey\WP\Filters;

class RenderTest extends TestCase {
  function setUp() {
    parent::setUp();
  }

  function testThrowsErrorWhenConstructionPlanIsEmpty() {
    $this->expectException(Exception::class);
    $cp = Render::fromConstructionPlan([]);
  }

  function testThrowsErrorWhenModuleFileDoesntExist() {
    Filters::expectApplied('WPStarter/defaultModulesPath')
    ->andReturn('');

    $this->expectException(Exception::class);

    $cp = Render::fromConstructionPlan([
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

    $html = Render::fromConstructionPlan($cp);
    $this->assertEquals($html, "<div>{$moduleName} result</div>\n");
  }

  function testRendersNestedModulesCorrectly() {
    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'SingleModule';
    $moduleData = [
      'test' => 'result'
    ];

    // check if filter ran exactly twice
    Filters::expectApplied('WPStarter/Render/renderModule')
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

    $html = Render::fromConstructionPlan($cp);

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

    Filters::expectApplied('WPStarter/Render/renderModule')
    ->once()
    ->with('', $moduleData)
    ->andReturn($shouldBeHtml);

    $html = Render::fromConstructionPlan($cp);
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
    Filters::expectApplied('WPStarter/Render/renderModule?name=' . $childModuleName)
    ->once()
    ->with('', $moduleData)
    ->andReturn($shouldBeChildOutput);

    $html = Render::fromConstructionPlan($cp);
    $this->assertEquals($html, $shouldBeHtml);
  }
}
