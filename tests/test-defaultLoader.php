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
    ->with(['WPStarter\DefaultLoader', 'addFilterConfigPath'], 999, 1);

    DefaultLoader::init();
  }

  function testAddsFilterForConfigFileLoader() {
    Filters::expectAdded('WPStarter/configFileLoader')
    ->once()
    ->with(['WPStarter\DefaultLoader', 'addFilterConfigFileLoader'], 999, 3);

    DefaultLoader::init();
  }

  function testAddsFilterForRenderModule() {
    Filters::expectAdded('WPStarter/renderModule')
    ->once()
    ->with(['WPStarter\DefaultLoader', 'addFilterRenderModule'], 999, 3);

    DefaultLoader::init();
  }

  function testAddsFilterForModulePath() {
    Filters::expectAdded('WPStarter/modulePath')
    ->once()
    ->with(['WPStarter\DefaultLoader', 'addFilterModulePath'], 999, 2);

    DefaultLoader::init();
  }

  function testAddsActionForRenderModule() {
    Actions::expectAdded('WPStarter/renderModule')
    ->once()
    ->with(['WPStarter\DefaultLoader', 'addActionRenderModule']);

    DefaultLoader::init();
  }

  function testReturnsAConfigPath() {
    $configPath = DefaultLoader::addFilterConfigPath(null, '');
    $this->assertEquals($configPath, TestHelper::getTemplateDirectory() . '/config');
  }

  function testLoadsAndDecodesJsonFile() {
    $configPath = TestHelper::getConfigPath() . 'exampleConfigWithSingleModule.json';
    $config = DefaultLoader::addFilterConfigFileLoader(null, '', $configPath);
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
  function testRenderThrowsErrorIfModuleFileIsADirectory() {
    $moduleName = 'SingleModule';
    $moduleData = [];
    $areaHtml = [];

    $this->expectException(Exception::class);

    Mockery::mock('alias:WPStarter\WPStarter')
    ->shouldReceive('getModulePath')
    ->once()
    ->with($moduleName)
    ->andReturn(TestHelper::getModulesPath() . $moduleName);

    DefaultLoader::addFilterRenderModule(null, $moduleName, $moduleData, $areaHtml);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testRenderThrowsErrorIfModuleFileDoesntExist() {
    $moduleName = 'SomeModuleThatDoesntExist';
    $moduleData = [];
    $areaHtml = [];

    $this->expectException(Exception::class);

    Mockery::mock('alias:WPStarter\WPStarter')
    ->shouldReceive('getModulePath')
    ->once()
    ->with($moduleName)
    ->andReturn('not/a/real/file.php');

    DefaultLoader::addFilterRenderModule(null, $moduleName, $moduleData, $areaHtml);
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

    Mockery::mock('alias:WPStarter\WPStarter')
    ->shouldReceive('getModulePath')
    ->once()
    ->with($moduleName)
    ->andReturn(TestHelper::getModulesPath() . $moduleName . '/index.php');

    Functions::expect('WPStarter\Helpers\extractNestedDataFromArray')
    ->andReturn('result');

    $output = DefaultLoader::addFilterRenderModule('', $moduleName, $moduleData, $areaHtml);

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

    Mockery::mock('alias:WPStarter\WPStarter')
    ->shouldReceive('getModulePath')
    ->times(2)
    ->with(Mockery::type('string'))
    ->andReturnUsing(['TestHelper', 'getModuleIndexPath']);

    Functions::expect('WPStarter\Helpers\extractNestedDataFromArray')
    ->andReturn('result');

    $areaHtml = [
      'area51' => DefaultLoader::addFilterRenderModule('', $childModuleName, $moduleData, [])
    ];
    $output = DefaultLoader::addFilterRenderModule('', $parentModuleName, $moduleData, $areaHtml);

    $this->assertEquals($output, "<div>{$parentModuleName} result<div>{$childModuleName} result</div>\n</div>\n");
  }

  function testThrowsErrorWhenModuleFolderNotFound() {
    $this->expectException(Exception::class);
    DefaultLoader::addActionRenderModule('not/a/real/path');
  }

  function testLoadsFunctionsPhpOnRegisterModule() {
    $moduleName = 'SingleModule';

    Filters::expectAdded("WPStarter/DataFilters/{$moduleName}/foo")
    ->once();

    DefaultLoader::addActionRenderModule(TestHelper::getModulePath(null, $moduleName));
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
    DefaultLoader::addActionRenderModule(TestHelper::getModulePath(null, $moduleName));
  }
}
