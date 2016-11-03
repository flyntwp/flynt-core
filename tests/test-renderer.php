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
use WP_Mock;

class RendererTest extends TestCase {
  /**
   * @expectedException Exception
   */
  function testEmptyConstructionPlan() {
    $cp = Renderer::fromConstructionPlan([]);
  }

  function testSingleModule() {
    $moduleName = 'SingleModule';
    $cp = [
      'name' => $moduleName,
      'data' => [
        'test' => 'result'
      ]
    ];

    WP_Mock::onFilter('WPStarter/defaultModulesPath')
    ->with('')
    ->reply(__DIR__ . '/assets/src/');
    $html = Renderer::fromConstructionPlan($cp);
    $this->assertEquals($html, "<div>{$moduleName} result</div>\n");
  }

  function testNestedModules() {
    WP_Mock::onFilter('WPStarter/defaultModulesPath')
    ->with('')
    ->reply(__DIR__ . '/assets/src/');

    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'SingleModule';
    $cp = [
      'name' => $parentModuleName,
      'data' => [
        'test' => 'result'
      ],
      'areas' => [
        'area51' => [
          [
            'name' => $childModuleName,
            'data' => [
              'test' => 'result'
            ]
          ]
        ]
      ]
    ];

    $html = Renderer::fromConstructionPlan($cp);
    $this->assertEquals($html, "<div>{$parentModuleName} result<div>{$childModuleName} result</div>\n</div>\n");
  }
}
