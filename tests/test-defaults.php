<?php
/**
 * Class DefaultsTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Default Loader test case.
 */

require_once dirname(__DIR__) . '/lib/WPStarter/Defaults.php';

use WPStarter\TestCase;
use WPStarter\Defaults;
use Brain\Monkey\WP\Filters;
use Brain\Monkey\WP\Actions;

class DefaultsTest extends TestCase {

  function setUp() {
    parent::setUp();
  }

  function tearDown() {
    parent::tearDown();
  }

  function testAddsFilterForConfigPath() {
    Filters::expectAdded('WPStarter/configPath')
    ->once()
    ->with(['WPStarter\Defaults', 'setConfigPath'], 999, 1);

    Defaults::init();
  }

  function testAddsFilterForConfigFileLoader() {
    Filters::expectAdded('WPStarter/configFileLoader')
    ->once()
    ->with(['WPStarter\Defaults', 'loadConfigFile'], 999, 3);

    Defaults::init();
  }

  function testAddsFilterForRenderModule() {
    Filters::expectAdded('WPStarter/renderModule')
    ->once()
    ->with(['WPStarter\Defaults', 'renderModule'], 999, 3);

    Defaults::init();
  }

  function testAddsFilterForModulePath() {
    Filters::expectAdded('WPStarter/modulePath')
    ->once()
    ->with(['WPStarter\Defaults', 'setModulePath'], 999, 2);

    Defaults::init();
  }

  function testAddsActionForRegisterModule() {
    Actions::expectAdded('WPStarter/registerModule')
    ->once()
    ->ordered()
    ->with(['WPStarter\Defaults', 'checkModuleFolder']);

    Actions::expectAdded('WPStarter/registerModule')
    ->once()
    ->ordered()
    ->with(['WPStarter\Defaults', 'loadFunctionsFile']);

    Defaults::init();
  }

  function testReturnsAConfigPath() {
    $configPath = Defaults::setConfigPath(null, '');
    $this->assertEquals($configPath, TestHelper::getTemplateDirectory() . '/config');
  }

  function testLoadsAndDecodesJsonFile() {
    $configPath = TestHelper::getConfigPath() . 'exampleConfigWithSingleModule.json';
    $config = Defaults::loadConfigFile(null, '', $configPath);
    $this->assertEquals($config, [
      'name' => 'SingleModule',
      'customData' => [
        'test' => 'result'
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testRenderShowsWarningIfModuleFileIsADirectory() {
    $moduleName = 'SingleModule';
    $moduleData = [];
    $areaHtml = [];

    $this->expectException('PHPUnit_Framework_Error_Warning');

    $this->mockModuleManager()
    ->shouldReceive('getModuleFilePath')
    ->once()
    ->with($moduleName)
    ->andReturn(TestHelper::getModulesPath() . $moduleName);

    $output = Defaults::renderModule(null, $moduleName, $moduleData, $areaHtml);
    $this->assertEquals($output, '');
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testRenderShowsWarningIfModuleFileDoesntExist() {
    $moduleName = 'SomeModuleThatDoesntExist';
    $moduleData = [];
    $areaHtml = [];

    $this->expectException('PHPUnit_Framework_Error_Warning');

    $this->mockModuleManager()
    ->shouldReceive('getModuleFilePath')
    ->once()
    ->with($moduleName)
    ->andReturn('not/a/real/file.php');

    $output = Defaults::renderModule(null, $moduleName, $moduleData, $areaHtml);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testRenderReturnsEmptyStringOnError() {
    $moduleName = 'SomeModuleThatDoesntExist';
    $moduleData = [];
    $areaHtml = [];

    $this->mockModuleManager()
    ->shouldReceive('getModuleFilePath')
    ->once()
    ->with($moduleName)
    ->andReturn('not/a/real/file.php');

    // suppress exception to get an output
    $output = @Defaults::renderModule(null, $moduleName, $moduleData, $areaHtml);
    $this->assertEquals($output, '');
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testRendersFileCorrectly() {
    $moduleName = 'SingleModule';
    $moduleData = [
      'test' => 'result'
    ];
    $areaHtml = [];

    $this->mockModuleManager()
    ->shouldReceive('getModuleFilePath')
    ->once()
    ->with($moduleName)
    ->andReturn(TestHelper::getModulesPath() . $moduleName . '/index.php');

    Mockery::mock('alias:WPStarter\Helpers')
    ->shouldReceive('extractNestedDataFromArray')
    ->andReturn('result');

    $output = Defaults::renderModule('', $moduleName, $moduleData, $areaHtml);

    $expectedHTML = "<div>SingleModule result</div>\n";

    $this->assertEquals($output, $expectedHTML);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testRendersNestedModulesCorrectly() {
    $parentModuleName = 'ModuleWithArea';
    $childModuleName = 'SingleModule';
    $moduleData = [
      'test' => 'result'
    ];

    $this->mockModuleManager()
    ->shouldReceive('getModuleFilePath')
    ->times(2)
    ->with(Mockery::type('string'))
    ->andReturnUsing(['TestHelper', 'getModuleIndexPath']);

    Mockery::mock('alias:WPStarter\Helpers')
    ->shouldReceive('extractNestedDataFromArray')
    ->andReturn('result');

    $areaHtml = [
      'area51' => Defaults::renderModule('', $childModuleName, $moduleData, [])
    ];
    $output = Defaults::renderModule('', $parentModuleName, $moduleData, $areaHtml);

    $this->assertEquals($output, "<div>{$parentModuleName} result<div>{$childModuleName} result</div>\n</div>\n");
  }

  function testShowsWarningWhenModuleFolderNotFound() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    Defaults::checkModuleFolder('not/a/real/path');
  }

  function testLoadsFunctionsPhpOnRegisterModule() {
    $moduleName = 'SingleModule';

    Filters::expectAdded("WPStarter/DataFilters/{$moduleName}/foo")
    ->once();

    Defaults::loadFunctionsFile(TestHelper::getModulePath(null, $moduleName));
  }

  /**
   * @runInSeparateProcess
   */
  function testDoesNotLoadFunctionsPhpOnRegisterModuleIfItDoesntExist() {
    // running this test separately to be able to see the error message
    $moduleName = 'ModuleWithoutFunctionsPhp';

    // make sure test file wasn't added by mistake
    $this->assertFileNotExists(TestHelper::getModulePath(null, $moduleName) . '/index.php');

    // this will throw an error if a file is required that doesn't exist
    Defaults::loadFunctionsFile(TestHelper::getModulePath(null, $moduleName));
  }

  // Helpers
  function mockModuleManager() {
    $moduleManagerMock = Mockery::mock('ModuleManager');

    Mockery::mock('alias:WPStarter\ModuleManager')
    ->shouldReceive('getInstance')
    ->andReturn($moduleManagerMock);

    return $moduleManagerMock;
  }
}
