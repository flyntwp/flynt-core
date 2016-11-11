<?php
/**
 * Class DefaultLoaderTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Default Loader test case.
 */

require_once dirname(__DIR__) . '/lib/WPStarter/DefaultLoader.php';

use WPStarter\TestCase;
use WPStarter\DefaultLoader;
use Brain\Monkey\WP\Filters;
use Brain\Monkey\WP\Actions;
use Brain\Monkey\Functions;

class DefaultLoaderTest extends TestCase {

  function setUp() {
    parent::setUp();
  }

  function tearDown() {
    parent::tearDown();
  }

  function testAddsFilterForConfigPath() {
    Filters::expectAdded('WPStarter/configPath')
    ->once()
    ->with(['WPStarter\DefaultLoader', 'setConfigPath'], 999, 1);

    DefaultLoader::init();
  }

  function testAddsFilterForConfigFileLoader() {
    Filters::expectAdded('WPStarter/configFileLoader')
    ->once()
    ->with(['WPStarter\DefaultLoader', 'loadConfigFile'], 999, 3);

    DefaultLoader::init();
  }

  function testAddsFilterForRenderModule() {
    Filters::expectAdded('WPStarter/renderModule')
    ->once()
    ->with(['WPStarter\DefaultLoader', 'renderModule'], 999, 3);

    DefaultLoader::init();
  }

  function testAddsFilterForModulePath() {
    Filters::expectAdded('WPStarter/modulePath')
    ->once()
    ->with(['WPStarter\DefaultLoader', 'setModulePath'], 999, 2);

    DefaultLoader::init();
  }

  function testAddsActionForRegisterModule() {
    Actions::expectAdded('WPStarter/registerModule')
    ->once()
    ->ordered()
    ->with(['WPStarter\DefaultLoader', 'checkModuleFolder']);

    Actions::expectAdded('WPStarter/registerModule')
    ->once()
    ->ordered()
    ->with(['WPStarter\DefaultLoader', 'loadFunctionsFile']);

    DefaultLoader::init();
  }

  function testReturnsAConfigPath() {
    $configPath = DefaultLoader::setConfigPath(null, '');
    $this->assertEquals($configPath, TestHelper::getTemplateDirectory() . '/config');
  }

  function testLoadsAndDecodesJsonFile() {
    $configPath = TestHelper::getConfigPath() . 'exampleConfigWithSingleModule.json';
    $config = DefaultLoader::loadConfigFile(null, '', $configPath);
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

    $output = DefaultLoader::renderModule(null, $moduleName, $moduleData, $areaHtml);
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

    $output = DefaultLoader::renderModule(null, $moduleName, $moduleData, $areaHtml);
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
    $output = @DefaultLoader::renderModule(null, $moduleName, $moduleData, $areaHtml);
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

    Functions::expect('WPStarter\Helpers\extractNestedDataFromArray')
    ->andReturn('result');

    $output = DefaultLoader::renderModule('', $moduleName, $moduleData, $areaHtml);

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

    Functions::expect('WPStarter\Helpers\extractNestedDataFromArray')
    ->andReturn('result');

    $areaHtml = [
      'area51' => DefaultLoader::renderModule('', $childModuleName, $moduleData, [])
    ];
    $output = DefaultLoader::renderModule('', $parentModuleName, $moduleData, $areaHtml);

    $this->assertEquals($output, "<div>{$parentModuleName} result<div>{$childModuleName} result</div>\n</div>\n");
  }

  function testShowsWarningWhenModuleFolderNotFound() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    DefaultLoader::checkModuleFolder('not/a/real/path');
  }

  function testLoadsFunctionsPhpOnRegisterModule() {
    $moduleName = 'SingleModule';

    Filters::expectAdded("WPStarter/DataFilters/{$moduleName}/foo")
    ->once();

    DefaultLoader::loadFunctionsFile(TestHelper::getModulePath(null, $moduleName));
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
    DefaultLoader::loadFunctionsFile(TestHelper::getModulePath(null, $moduleName));
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
