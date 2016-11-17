<?php
/**
 * Class RenderTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Render test case.
 */

require_once dirname(__DIR__) . '/lib/WPStarter/Render.php';

use WPStarter\TestCase;
use WPStarter\Render;
use Brain\Monkey\WP\Filters;

class RenderTest extends TestCase {
  function setUp() {
    parent::setUp();
  }

  function testShowWarningWhenConstructionPlanIsEmpty() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    $cp = Render::fromConstructionPlan([]);
  }

  function testReturnEmptyStringWhenConstructionPlanIsEmpty() {
    $cp = @Render::fromConstructionPlan([]);
    $this->assertEquals($cp, '');
  }

  function testAppliesCustomHtmlHook() {
    // test whether custom html can be used
    $moduleName = 'SingleModule';
    $cp = [
      'name' => $moduleName,
      'data' => []
    ];

    $shouldBeHtml = "<div>{$moduleName} After Filter Hook</div>\n";

    Filters::expectApplied('WPStarter/renderModule')
    ->once()
    ->with(Mockery::mustBe(null), Mockery::type('string'), Mockery::type('array'), Mockery::type('array'))
    ->andReturn($shouldBeHtml);

    $html = Render::fromConstructionPlan($cp);
    $this->assertEquals($html, $shouldBeHtml);
  }

  function testAppliesCustomHtmlHookToANestedModule() {
    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'SingleModule';

    // test whether custom html can be used
    $cp = [
      'name' => $parentModuleName,
      'data' => [],
      'areas' => [
        'area51' => [
          [
            'name' => $childModuleName,
            'data' => []
          ]
        ]
      ]
    ];

    $shouldBeChildOutput = "<div>{$childModuleName} After Filter Hook</div>\n";
    $shouldBeHtml = "<div>{$parentModuleName} result" . $shouldBeChildOutput . "</div>\n";

    Filters::expectApplied('WPStarter/renderModule')
    ->times(2)
    ->with(Mockery::mustBe(null), Mockery::type('string'), Mockery::type('array'), Mockery::type('array'))
    ->andReturn($shouldBeHtml);

    // Specific Filters renderModule?name=SingleModule for example
    Filters::expectApplied('WPStarter/renderModule?name=' . $childModuleName)
    ->once()
    ->with(Mockery::type('string'), Mockery::type('string'), Mockery::type('array'), Mockery::type('array'))
    ->andReturn($shouldBeChildOutput);

    $html = Render::fromConstructionPlan($cp);
    $this->assertEquals($html, $shouldBeHtml);
  }
}
