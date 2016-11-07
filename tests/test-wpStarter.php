<?php

/**
 * Class WPStarterTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Construction plan test case.
 */

require_once dirname(__DIR__) . '/lib/WPStarter/WPStarter.php';

use WPStarter\TestCase;
use WPStarter\WPStarter;
use Brain\Monkey\WP\Filters;

class WPStarterTest extends TestCase {
  protected function setUp() {
    parent::setUp();

    // reset private static modules array in WPStarter
    $reflectedClass = new \ReflectionClass(WPStarter::class);
    $reflectedProperty = $reflectedClass->getProperty('modules');
    $reflectedProperty->setAccessible(true);
    $reflectedProperty = $reflectedProperty->setValue([]);
  }

  protected function tearDown() {
    parent::tearDown();
  }

  public function testLoadsFunctionsPhpOnRegisterModule() {
    $moduleName = 'SingleModule';

    Filters::expectAdded("WPStarter/DataFilters/{$moduleName}/foo")
    ->once();

    WPStarter::registerModule($moduleName);
  }

  public function testThrowsErrorWhenModuleFolderNotFound() {
    $this->expectException(Exception::class);
    WPStarter::registerModule('NotARealModule');
  }

  public function testModuleIsAddedToArray() {
    $moduleName = 'SingleModule';
    WPStarter::registerModule($moduleName);

    $modules = WPStarter::getModuleList();
    $this->assertEquals($modules, [$moduleName => TestHelper::getModulesPath() . $moduleName]);
  }

  public function testModuleIsOnlyAddedToArrayOnce() {
    $moduleName = 'SingleModule';
    WPStarter::registerModule($moduleName);

    $this->expectException(Exception::class);
    WPStarter::registerModule($moduleName);
  }

  public function testReturnsModuleList() {
    $moduleA = 'SingleModule';
    $moduleB = 'ModuleWithArea';

    WPStarter::registerModule($moduleA);
    $this->assertEquals(WPStarter::getModuleList(), [
      'SingleModule' => TestHelper::getModulesPath() . $moduleA
    ]);

    WPStarter::registerModule($moduleB);
    $this->assertEquals(WPStarter::getModuleList(), [
      'SingleModule' => TestHelper::getModulesPath() . $moduleA,
      'ModuleWithArea' => TestHelper::getModulesPath() . $moduleB
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  public function testEchoesHtmlFromConfiguration() {
    $config = [
      'name' => 'SingleModule',
      'customData' => [
        'test' => 'result'
      ]
    ];

    $constructionPlan = [
      'name' => 'SingleModule',
      'data' => [
        'test' => 'result'
      ]
    ];

    Mockery::mock('alias:WPStarter\ConstructionPlan')
    ->shouldReceive('fromConfig')
    ->once()
    ->with($config, [])
    ->andReturn($constructionPlan);

    Mockery::mock('alias:WPStarter\Render')
    ->shouldReceive('fromConstructionPlan')
    ->once()
    ->with($constructionPlan)
    ->andReturn('test');

    $this->expectOutputString('test');
    WPStarter::echoHtmlFromConfig($config);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  public function testEchoesHtmlFromConfigurationFile() {
    $configFileName = 'exampleConfigWithSingleModule.json';

    $constructionPlan = [
      'name' => 'SingleModule',
      'data' => [
        'test' => 'result'
      ]
    ];

    Mockery::mock('alias:WPStarter\ConstructionPlan')
    ->shouldReceive('fromConfigFile')
    ->once()
    ->with($configFileName, [])
    ->andReturn($constructionPlan);

    Mockery::mock('alias:WPStarter\Render')
    ->shouldReceive('fromConstructionPlan')
    ->once()
    ->with($constructionPlan)
    ->andReturn('test');

    $this->expectOutputString('test');
    WPStarter::echoHtmlFromConfigFile($configFileName);
  }
}
