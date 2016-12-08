<?php
/**
 * Class DefaultsTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Default Loader test case.
 */

require_once dirname(__DIR__) . '/lib/Flynt/Defaults.php';

use Flynt\TestCase;
use Flynt\Defaults;
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
    Filters::expectAdded('Flynt/configPath')
    ->once()
    ->with(['Flynt\Defaults', 'setConfigPath'], 999, 2);

    Defaults::init();
  }

  function testAddsFilterForConfigFileLoader() {
    Filters::expectAdded('Flynt/configFileLoader')
    ->once()
    ->with(['Flynt\Defaults', 'loadConfigFile'], 999, 3);

    Defaults::init();
  }

  function testAddsFilterForRenderModule() {
    Filters::expectAdded('Flynt/renderModule')
    ->once()
    ->with(['Flynt\Defaults', 'renderModule'], 999, 4);

    Defaults::init();
  }

  function testAddsFilterForModulePath() {
    Filters::expectAdded('Flynt/modulePath')
    ->once()
    ->with(['Flynt\Defaults', 'setModulePath'], 999, 2);

    Defaults::init();
  }

  function testAddsActionForRegisterModule() {
    Actions::expectAdded('Flynt/registerModule')
    ->once()
    ->ordered()
    ->with(['Flynt\Defaults', 'checkModuleFolder']);

    Actions::expectAdded('Flynt/registerModule')
    ->once()
    ->ordered()
    ->with(['Flynt\Defaults', 'loadFunctionsFile']);

    Defaults::init();
  }

  function testReturnsAConfigPath() {
    $configPath = Defaults::setConfigPath(null, 'config.json');
    $this->assertEquals($configPath, TestHelper::getTemplateDirectory() . '/config/config.json');
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

    Mockery::mock('alias:Flynt\Helpers')
    ->shouldReceive('extractNestedDataFromArray')
    ->andReturn('result');

    $output = Defaults::renderModule(null, $moduleName, $moduleData, $areaHtml);

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

    Mockery::mock('alias:Flynt\Helpers')
    ->shouldReceive('extractNestedDataFromArray')
    ->andReturn('result');

    $areaHtml = [
      'area51' => Defaults::renderModule(null, $childModuleName, $moduleData, [])
    ];
    $output = Defaults::renderModule(null, $parentModuleName, $moduleData, $areaHtml);

    $this->assertEquals($output, "<div>{$parentModuleName} result<div>{$childModuleName} result</div>\n</div>\n");
  }

  function testShowsWarningWhenModuleFolderNotFound() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    Defaults::checkModuleFolder('not/a/real/path');
  }

  function testLoadsFunctionsPhpOnRegisterModule() {
    $moduleName = 'SingleModule';

    Filters::expectAdded("Flynt/DataFilters/{$moduleName}/foo")
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

  function testIsGettingDefaultModulesDirectory() {
    $dir = Defaults::getModulesDirectory();
    $this->assertEquals($dir, TestHelper::getTemplateDirectory() . '/Modules');
  }

  // Helpers
  function mockModuleManager() {
    $moduleManagerMock = Mockery::mock('ModuleManager');

    Mockery::mock('alias:Flynt\ModuleManager')
    ->shouldReceive('getInstance')
    ->andReturn($moduleManagerMock);

    return $moduleManagerMock;
  }
}
