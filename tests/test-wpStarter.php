<?php

/**
 * Class WPStarterTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * WPStarter API functions test case.
 */

require_once dirname(__DIR__) . '/lib/WPStarter.php';

use WPStarter\TestCase;
use Brain\Monkey\WP\Actions;
use Brain\Monkey\WP\Filters;
use function WPStarter\echoHtmlFromConfig;
use function WPStarter\echoHtmlFromConfigFile;
use function WPStarter\getHtmlFromConfig;
use function WPStarter\getHtmlFromConfigFile;
use function WPStarter\registerModule;
use function WPStarter\registerModules;

class WPStarterTest extends TestCase {
  protected function setUp() {
    parent::setUp();
  }

  protected function tearDown() {
    parent::tearDown();
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testRegisterModuleIsForwarded() {
    $moduleName = 'ModuleWithArea';
    $modulePath = TestHelper::getModulesPath() . 'SingleModule';

    $moduleManagerMock = Mockery::mock('ModuleManager');

    Mockery::mock('alias:WPStarter\ModuleManager')
    ->shouldReceive('getInstance')
    ->once()
    ->andReturn($moduleManagerMock);

    $moduleManagerMock
    ->shouldReceive('registerModule')
    ->with($moduleName, $modulePath)
    ->once();

    registerModule($moduleName, $modulePath);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testRegistersModulesFromArray() {
    $moduleManagerMock = Mockery::mock('ModuleManager');

    Mockery::mock('alias:WPStarter\ModuleManager')
    ->shouldReceive('getInstance')
    ->times(3)
    ->andReturn($moduleManagerMock);

    $moduleManagerMock
    ->shouldReceive('registerModule')
    ->with('ModuleA', null)
    ->ordered()
    ->once();

    $moduleManagerMock
    ->shouldReceive('registerModule')
    ->with('ModuleB', 'some/path')
    ->ordered()
    ->once();

    $moduleManagerMock
    ->shouldReceive('registerModule')
    ->with('ModuleC', null)
    ->ordered()
    ->once();

    $modulesWithPaths = [
      'ModuleA' => null,
      'ModuleB' => 'some/path',
      'ModuleC' => null
    ];

    registerModules($modulesWithPaths);

    $moduleManagerMock
    ->shouldReceive('registerModule')
    ->with('ModuleD', null)
    ->ordered()
    ->once();

    $moduleManagerMock
    ->shouldReceive('registerModule')
    ->with('ModuleE', null)
    ->ordered()
    ->once();

    $moduleManagerMock
    ->shouldReceive('registerModule')
    ->with('ModuleF', null)
    ->ordered()
    ->once();

    $modulesWithoutPaths = [
      'ModuleD',
      'ModuleE',
      'ModuleF'
    ];

    registerModules($modulesWithoutPaths);

    $moduleManagerMock
    ->shouldReceive('registerModule')
    ->with('ModuleG', null)
    ->ordered()
    ->once();

    $moduleManagerMock
    ->shouldReceive('registerModule')
    ->with('ModuleH', null)
    ->ordered()
    ->once();

    $moduleManagerMock
    ->shouldReceive('registerModule')
    ->with('ModuleI', 'some/path')
    ->ordered()
    ->once();

    $modulesMixed = [
      'ModuleG',
      'ModuleH' => null,
      'ModuleI' => 'some/path'
    ];

    registerModules($modulesMixed);
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
    ->with($config)
    ->andReturn($constructionPlan);

    Mockery::mock('alias:WPStarter\Render')
    ->shouldReceive('fromConstructionPlan')
    ->once()
    ->with($constructionPlan)
    ->andReturn('test');

    $this->expectOutputString('test');
    echoHtmlFromConfig($config);
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
    ->with($configFileName)
    ->andReturn($constructionPlan);

    Mockery::mock('alias:WPStarter\Render')
    ->shouldReceive('fromConstructionPlan')
    ->once()
    ->with($constructionPlan)
    ->andReturn('test');

    $this->expectOutputString('test');
    echoHtmlFromConfigFile($configFileName);
  }
}
