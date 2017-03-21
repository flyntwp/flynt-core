<?php
/**
 * Class RenderTest
 *
 * @package Flynt_Core
 */

/**
 * Render test case.
 */

namespace Flynt\Tests;

require_once dirname(__DIR__) . '/lib/Flynt/Render.php';

use Flynt\Tests\TestCase;
use Flynt\Render;
use Brain\Monkey\WP\Filters;

class RenderTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

  // tested cases:
  // - empty string
  // - number
  // - empty array
  // - array of strings
  // - invalid area (not an array)
  // - missing params (data and name) in constructionPlan
    public function badValues()
    {
        return [
        [''],
        [5],
        [[]],
        [['']],
        [[
        'name' => 'test',
        'data' => 'dataNotArray'
        ]],
        [[
        'name' => [],
        'data' => 'dataNotArray'
        ]],
        [[
        'name' => 'test',
        'data' => [],
        'areas' => [
          []
        ]
        ]],
        [[
        'name' => 'test',
        'data' => [],
        'areas' => [
          'testArea' => [
            'test'
          ]
        ]
        ]],
        [[
        'name' => 'test',
        'data' => [],
        'areas' => [
          'testArea' => [
            'name' => 'test',
            'data' => []
          ]
        ]
        ]]
        ];
    }

  /**
   * @dataProvider badValues
   */
    public function testShowWarningWhenConstructionPlanIsInvalid($badValue)
    {
        $this->expectException('PHPUnit_Framework_Error_Warning');
        $cp = Render::fromConstructionPlan($badValue);
    }

  /**
   * @dataProvider badValues
   */
    public function testReturnsEmptyStringWhenConstructionPlanIsInvalid($badValue)
    {
        $cp = @Render::fromConstructionPlan($badValue);
        $this->assertEquals($cp, '');
    }

    public function testAppliesCustomHtmlHook()
    {
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

    public function testAppliesCustomHtmlHookToANestedComponent()
    {
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
