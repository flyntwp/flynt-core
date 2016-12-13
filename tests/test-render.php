<?php
/**
 * Class RenderTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Render test case.
 */

require_once dirname(__DIR__) . '/lib/Flynt/Render.php';

use Flynt\TestCase;
use Flynt\Render;
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
    $componentName = 'SingleComponent';
    $cp = [
      'name' => $componentName,
      'data' => []
    ];

    $shouldBeHtml = "<div>{$componentName} After Filter Hook</div>\n";

    Filters::expectApplied('Flynt/renderComponent')
    ->once()
    ->with(Mockery::mustBe(null), Mockery::type('string'), Mockery::type('array'), Mockery::type('array'))
    ->andReturn($shouldBeHtml);

    $html = Render::fromConstructionPlan($cp);
    $this->assertEquals($html, $shouldBeHtml);
  }

  function testAppliesCustomHtmlHookToANestedComponent() {
    $parentComponentName = 'ComponentWithArea';
    $childComponentName = 'SingleComponent';

    // test whether custom html can be used
    $cp = [
      'name' => $parentComponentName,
      'data' => [],
      'areas' => [
        'area51' => [
          [
            'name' => $childComponentName,
            'data' => []
          ]
        ]
      ]
    ];

    $shouldBeChildOutput = "<div>{$childComponentName} After Filter Hook</div>\n";
    $shouldBeHtml = "<div>{$parentComponentName} result" . $shouldBeChildOutput . "</div>\n";

    Filters::expectApplied('Flynt/renderComponent')
    ->times(2)
    ->with(Mockery::mustBe(null), Mockery::type('string'), Mockery::type('array'), Mockery::type('array'))
    ->andReturn($shouldBeHtml);

    // Specific Filters renderComponent?name=SingleComponent for example
    Filters::expectApplied('Flynt/renderComponent?name=' . $childComponentName)
    ->once()
    ->with(Mockery::type('string'), Mockery::type('string'), Mockery::type('array'), Mockery::type('array'))
    ->andReturn($shouldBeChildOutput);

    $html = Render::fromConstructionPlan($cp);
    $this->assertEquals($html, $shouldBeHtml);
  }
}
