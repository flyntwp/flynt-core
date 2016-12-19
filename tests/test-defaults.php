<?php
/**
 * Class DefaultsTest
 *
 * @package Flynt_Core
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

  function testAddsFilterForRenderComponent() {
    Filters::expectAdded('Flynt/renderComponent')
    ->once()
    ->with(['Flynt\Defaults', 'renderComponent'], 999, 4);

    Defaults::init();
  }

  function testAddsFilterForComponentPath() {
    Filters::expectAdded('Flynt/componentPath')
    ->once()
    ->with(['Flynt\Defaults', 'setComponentPath'], 999, 2);

    Defaults::init();
  }

  function testAddsActionForRegisterComponent() {
    Actions::expectAdded('Flynt/registerComponent')
    ->once()
    ->ordered()
    ->with(['Flynt\Defaults', 'checkComponentFolder']);

    Actions::expectAdded('Flynt/registerComponent')
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
    $configPath = TestHelper::getConfigPath() . 'exampleConfigWithSingleComponent.json';
    $config = Defaults::loadConfigFile(null, '', $configPath);
    $this->assertEquals($config, [
      'name' => 'SingleComponent',
      'customData' => [
        'test' => 'result'
      ]
    ]);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testRenderShowsWarningIfComponentFileIsADirectory() {
    $componentName = 'SingleComponent';
    $componentData = [];
    $areaHtml = [];

    $this->expectException('PHPUnit_Framework_Error_Warning');

    $this->mockComponentManager()
    ->shouldReceive('getComponentFilePath')
    ->once()
    ->with($componentName)
    ->andReturn(TestHelper::getComponentsPath() . $componentName);

    $output = Defaults::renderComponent(null, $componentName, $componentData, $areaHtml);
    $this->assertEquals($output, '');
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testRenderShowsWarningIfComponentFileDoesntExist() {
    $componentName = 'SomeComponentThatDoesntExist';
    $componentData = [];
    $areaHtml = [];

    $this->expectException('PHPUnit_Framework_Error_Warning');

    $this->mockComponentManager()
    ->shouldReceive('getComponentFilePath')
    ->once()
    ->with($componentName)
    ->andReturn('not/a/real/file.php');

    $output = Defaults::renderComponent(null, $componentName, $componentData, $areaHtml);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testRenderReturnsEmptyStringOnError() {
    $componentName = 'SomeComponentThatDoesntExist';
    $componentData = [];
    $areaHtml = [];

    $this->mockComponentManager()
    ->shouldReceive('getComponentFilePath')
    ->once()
    ->with($componentName)
    ->andReturn('not/a/real/file.php');

    // suppress exception to get an output
    $output = @Defaults::renderComponent(null, $componentName, $componentData, $areaHtml);
    $this->assertEquals($output, '');
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testRendersFileCorrectly() {
    $componentName = 'SingleComponent';
    $componentData = [
      'test' => 'result'
    ];
    $areaHtml = [];

    $this->mockComponentManager()
    ->shouldReceive('getComponentFilePath')
    ->once()
    ->with($componentName)
    ->andReturn(TestHelper::getComponentsPath() . $componentName . '/index.php');

    Mockery::mock('alias:Flynt\Helpers')
    ->shouldReceive('extractNestedDataFromArray')
    ->andReturn('result');

    $output = Defaults::renderComponent(null, $componentName, $componentData, $areaHtml);

    $expectedHTML = "<div>SingleComponent result</div>\n";

    $this->assertEquals($output, $expectedHTML);
  }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
  function testRendersNestedComponentsCorrectly() {
    $parentComponentName = 'ComponentWithArea';
    $childComponentName = 'SingleComponent';
    $componentData = [
      'test' => 'result'
    ];

    $this->mockComponentManager()
    ->shouldReceive('getComponentFilePath')
    ->times(2)
    ->with(Mockery::type('string'))
    ->andReturnUsing(['TestHelper', 'getComponentIndexPath']);

    Mockery::mock('alias:Flynt\Helpers')
    ->shouldReceive('extractNestedDataFromArray')
    ->andReturn('result');

    $areaHtml = [
      'area51' => Defaults::renderComponent(null, $childComponentName, $componentData, [])
    ];
    $output = Defaults::renderComponent(null, $parentComponentName, $componentData, $areaHtml);

    $this->assertEquals($output, "<div>{$parentComponentName} result<div>{$childComponentName} result</div>\n</div>\n");
  }

  function testShowsWarningWhenComponentFolderNotFound() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    Defaults::checkComponentFolder('not/a/real/path');
  }

  function testLoadsFunctionsPhpOnRegisterComponent() {
    $componentName = 'SingleComponent';

    Filters::expectAdded("Flynt/DataFilters/{$componentName}/foo")
    ->once();

    Defaults::loadFunctionsFile(TestHelper::getComponentPath(null, $componentName));
  }

  /**
   * @runInSeparateProcess
   */
  function testDoesNotLoadFunctionsPhpOnRegisterComponentIfItDoesntExist() {
    // running this test separately to be able to see the error message
    $componentName = 'ComponentWithoutFunctionsPhp';

    // make sure test file wasn't added by mistake
    $this->assertFileNotExists(TestHelper::getComponentPath(null, $componentName) . '/index.php');

    // this will throw an error if a file is required that doesn't exist
    Defaults::loadFunctionsFile(TestHelper::getComponentPath(null, $componentName));
  }

  function testIsGettingDefaultComponentsDirectory() {
    $dir = Defaults::getComponentsDirectory();
    $this->assertEquals($dir, TestHelper::getTemplateDirectory() . '/Components');
  }

  // Helpers
  function mockComponentManager() {
    $componentManagerMock = Mockery::mock('ComponentManager');

    Mockery::mock('alias:Flynt\ComponentManager')
    ->shouldReceive('getInstance')
    ->andReturn($componentManagerMock);

    return $componentManagerMock;
  }
}
