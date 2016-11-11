<?php

/**
 * Class WPStarterTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * WPStarter test case.
 */

require_once dirname(__DIR__) . '/lib/WPStarter/WPStarter.php';

use WPStarter\TestCase;
use WPStarter\WPStarter;
use Brain\Monkey\WP\Actions;
use Brain\Monkey\WP\Filters;

class WPStarterTest extends TestCase {
  protected function setUp() {
    parent::setUp();

    // reset private static modules array in WPStarter
    $reflectedClass = new ReflectionClass(WPStarter::class);
    $reflectedProperty = $reflectedClass->getProperty('modules');
    $reflectedProperty->setAccessible(true);
    $reflectedProperty = $reflectedProperty->setValue([]);
  }

  protected function tearDown() {
    parent::tearDown();
  }

  public function testRegisterModuleUsesOptionalPathParameter() {
    $moduleName = 'ModuleWithArea';
    $modulePath = TestHelper::getModulesPath() . 'SingleModule';

    Filters::expectApplied('WPStarter/modulePath')
    ->with($modulePath, $moduleName)
    ->once();

    WPStarter::registerModule($moduleName, $modulePath);
  }

  public function testModuleIsAddedToArray() {
    $moduleName = 'SingleModule';

    Filters::expectApplied('WPStarter/modulePath')
    ->andReturnUsing(['TestHelper', 'getModulePath']);

    WPStarter::registerModule($moduleName);

    $modules = WPStarter::getModuleList();
    $this->assertEquals($modules, [$moduleName => TestHelper::getModulesPath() . $moduleName . '/']);
  }

  public function testShowsWarningWhenModuleIsAddedMoreThanOnce() {
    $moduleName = 'SingleModule';
    WPStarter::registerModule($moduleName);

    $this->expectException('PHPUnit_Framework_Error_Warning');

    WPStarter::registerModule($moduleName);
  }

  public function testModuleIsOnlyAddedToArrayOnce() {
    $moduleName = 'SingleModule';
    WPStarter::registerModule($moduleName);

    Filters::expectApplied('WPStarter/modulePath')
    ->never();

    @WPStarter::registerModule($moduleName);
  }

  public function testDoesRegisterModuleAction() {
    $moduleName = 'SingleModule';
    $modulePath = TestHelper::getModulesPath() . $moduleName . '/';

    Filters::expectApplied('WPStarter/modulePath')
    ->andReturnUsing(['TestHelper', 'getModulePath']);

    Actions::expectFired('WPStarter/registerModule')
    ->with($modulePath, $moduleName);

    Actions::expectFired("WPStarter/registerModule?name={$moduleName}")
    ->with($modulePath);

    WPStarter::registerModule($moduleName);
  }

  public function testReturnsModuleList() {
    $moduleA = 'SingleModule';
    $moduleB = 'ModuleWithArea';

    Filters::expectApplied('WPStarter/modulePath')
    ->andReturnUsing(['TestHelper', 'getModulePath']);

    WPStarter::registerModule($moduleA);
    $this->assertEquals(WPStarter::getModuleList(), [
      'SingleModule' => TestHelper::getModulesPath() . $moduleA . '/'
    ]);

    WPStarter::registerModule($moduleB);
    $this->assertEquals(WPStarter::getModuleList(), [
      'SingleModule' => TestHelper::getModulesPath() . $moduleA . '/',
      'ModuleWithArea' => TestHelper::getModulesPath() . $moduleB . '/'
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

    Mockery::mock('alias:WPStarter\BuildConstructionPlan')
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

    Mockery::mock('alias:WPStarter\BuildConstructionPlan')
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

  function testGetsModuleFile() {
    $moduleName = 'SingleModule';

    // mock default functionality for path on registerModule
    Filters::expectApplied('WPStarter/modulePath')
    ->andReturnUsing(['TestHelper', 'getModulePath']);
    WPStarter::registerModule($moduleName);

    // test default
    $path = WPStarter::getModuleFilePath($moduleName);
    $this->assertEquals($path, TestHelper::getModulePath(null, $moduleName) . '/index.php');

    // test second parameter
    $fileName = 'index.html';
    $path = WPStarter::getModuleFilePath($moduleName, $fileName);
    $this->assertEquals($path, TestHelper::getModulePath(null, $moduleName) . '/' . $fileName);
  }

  function testGetModuleFilePathShowsWarningOnUnregisteredModuleParam() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    $path = WPStarter::getModuleFilePath('SomeModuleName');
  }

  function testGetModuleFilePathReturnsFalseOnUnregisteredModuleParam() {
    $path = @WPStarter::getModuleFilePath('SomeModuleName');
    $this->assertFalse($path);
  }

  function testGetModuleFilePathShowsWarningOnIncorrectFileName() {
    $moduleName = 'SingleModule';

    Filters::expectApplied('WPStarter/modulePath')
    ->andReturnUsing(['TestHelper', 'getModulePath']);
    WPStarter::registerModule($moduleName);

    $this->expectException('PHPUnit_Framework_Error_Warning');
    $path = WPStarter::getModuleFilePath($moduleName, 'doesNotExist.something');
  }

  function testGetModuleFilePathReturnsFalseOnIncorrectFileName() {
    $moduleName = 'SingleModule';

    Filters::expectApplied('WPStarter/modulePath')
    ->andReturnUsing(['TestHelper', 'getModulePath']);
    WPStarter::registerModule($moduleName);

    $path = @WPStarter::getModuleFilePath($moduleName, 'doesNotExist.something');
    $this->assertFalse($path);
  }
}
