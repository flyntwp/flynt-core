<?php

/**
 * Class FlyntTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Flynt API functions test case.
 */

require_once dirname(__DIR__) . '/lib/Flynt.php';

use Flynt\TestCase;
use Brain\Monkey\WP\Actions;
use Brain\Monkey\WP\Filters;
use function Flynt\echoHtmlFromConfig;
use function Flynt\echoHtmlFromConfigFile;
use function Flynt\getHtmlFromConfig;
use function Flynt\getHtmlFromConfigFile;
use function Flynt\registerModule;
use function Flynt\registerModules;
use function Flynt\initDefaults;

class FlyntTest extends TestCase {
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

    Mockery::mock('alias:Flynt\ModuleManager')
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

    Mockery::mock('alias:Flynt\ModuleManager')
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

    Mockery::mock('alias:Flynt\BuildConstructionPlan')
    ->shouldReceive('fromConfig')
    ->once()
    ->with($config)
    ->andReturn($constructionPlan);

    Mockery::mock('alias:Flynt\Render')
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

    Mockery::mock('alias:Flynt\BuildConstructionPlan')
    ->shouldReceive('fromConfigFile')
    ->once()
    ->with($configFileName)
    ->andReturn($constructionPlan);

    Mockery::mock('alias:Flynt\Render')
    ->shouldReceive('fromConstructionPlan')
    ->once()
    ->with($constructionPlan)
    ->andReturn('test');

    $this->expectOutputString('test');
    echoHtmlFromConfigFile($configFileName);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  public function testCallsDefaultsInitFunction() {
    Mockery::mock('alias:Flynt\Defaults')
    ->shouldReceive('init')
    ->once();

    initDefaults();
  }
}
