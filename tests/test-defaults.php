<?php
/**
 * Class DefaultsTest
 *
 * @package Flynt_Core
 */

/**
 * Default Loader test case.
 */

namespace Flynt\Tests;

require_once dirname(__DIR__) . '/lib/Flynt/Defaults.php';

use Mockery;
use Brain\Monkey\WP\Filters;
use Brain\Monkey\WP\Actions;
use Flynt\Tests\TestCase;
use Flynt\Tests\TestHelper;
use Flynt\Defaults;

class DefaultsTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testAddsFilterForConfigPath()
    {
        Filters::expectAdded('Flynt/configPath')
        ->once()
        ->with(['Flynt\Defaults', 'setConfigPath'], 999, 2);

        Defaults::init();
    }

    public function testAddsFilterForConfigFileLoader()
    {
        Filters::expectAdded('Flynt/configFileLoader')
        ->once()
        ->with(['Flynt\Defaults', 'loadConfigFile'], 999, 3);

        Defaults::init();
    }

    public function testAddsFilterForRenderComponent()
    {
        Filters::expectAdded('Flynt/renderComponent')
        ->once()
        ->with(['Flynt\Defaults', 'renderComponent'], 999, 4);

        Defaults::init();
    }

    public function testAddsFilterForComponentPath()
    {
        Filters::expectAdded('Flynt/componentPath')
        ->once()
        ->with(['Flynt\Defaults', 'setComponentPath'], 999, 2);

        Defaults::init();
    }

    public function testAddsActionForRegisterComponent()
    {
        Actions::expectAdded('Flynt/registerComponent')
        ->once()
        ->with(['Flynt\Defaults', 'loadFunctionsFile']);

        Defaults::init();
    }

    public function testReturnsAConfigPath()
    {
        $configPath = Defaults::setConfigPath(null, 'config.json');
        $this->assertEquals($configPath, TestHelper::getTemplateDirectory() . '/config/config.json');
    }

    public function testLoadsAndDecodesJsonFile()
    {
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
    public function testRenderShowsWarningIfComponentFileIsADirectory()
    {
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
    public function testRenderShowsWarningIfComponentFileDoesntExist()
    {
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
    public function testRenderReturnsEmptyStringOnError()
    {
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
    public function testRendersFileCorrectly()
    {
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
    public function testRendersNestedComponentsCorrectly()
    {
        $parentComponentName = 'ComponentWithArea';
        $childComponentName = 'SingleComponent';
        $componentData = [
        'test' => 'result'
        ];

        $this->mockComponentManager()
        ->shouldReceive('getComponentFilePath')
        ->times(2)
        ->with(Mockery::type('string'))
        ->andReturnUsing(['\\Flynt\\Tests\\TestHelper', 'getComponentIndexPath']);

        Mockery::mock('alias:Flynt\Helpers')
        ->shouldReceive('extractNestedDataFromArray')
        ->andReturn('result');

        $areaHtml = [
        'area51' => Defaults::renderComponent(null, $childComponentName, $componentData, [])
        ];
        $output = Defaults::renderComponent(null, $parentComponentName, $componentData, $areaHtml);

        $this->assertEquals($output, "<div>{$parentComponentName} result<div>{$childComponentName} result</div>\n</div>\n");
    }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
    public function testLoadsFunctionsPhpOnRegisterComponent()
    {
        $componentName = 'SingleComponent';
        $componentPath = TestHelper::getComponentPath(null, $componentName);

        $this->mockComponentManager()
        ->shouldReceive('getComponentFilePath')
        ->with($componentName, 'functions.php')
        ->andReturn($componentPath . '/functions.php');

        // checking if filter in required component file is added
        Filters::expectAdded("Flynt/DataFilters/{$componentName}/foo")
        ->once();

        Defaults::loadFunctionsFile($componentName);
    }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
    public function testDoesNotLoadFunctionsPhpOnRegisterComponentIfItDoesntExist()
    {
        // running this test separately to be able to see the error message
        $componentName = 'ComponentWithoutFunctionsPhp';

        $this->mockComponentManager()
        ->shouldReceive('getComponentFilePath')
        ->with($componentName, 'functions.php')
        ->andReturn(false);

        // this will throw an error if a file is required that doesn't exist
        Defaults::loadFunctionsFile($componentName);
    }

    public function testIsGettingDefaultComponentsDirectory()
    {
        $dir = Defaults::getComponentsDirectory();
        $this->assertEquals($dir, TestHelper::getTemplateDirectory() . '/Components');
    }

  // Helpers
    public function mockComponentManager()
    {
        $componentManagerMock = Mockery::mock('ComponentManager');

        Mockery::mock('alias:Flynt\ComponentManager')
        ->shouldReceive('getInstance')
        ->andReturn($componentManagerMock);

        return $componentManagerMock;
    }
}
