<?php

/**
 * Class FlyntTest
 *
 * @package Flynt_Core
 */

/**
 * Flynt API functions test case.
 */

namespace Flynt\Tests;

require_once dirname(__DIR__) . '/lib/Flynt.php';

use Flynt\Tests\TestCase;
use Brain\Monkey\WP\Actions;
use Brain\Monkey\WP\Filters;
use function Flynt\echoHtmlFromConfig;
use function Flynt\echoHtmlFromConfigFile;
use function Flynt\getHtmlFromConfig;
use function Flynt\getHtmlFromConfigFile;
use function Flynt\registerComponent;
use function Flynt\registerComponents;
use function Flynt\initDefaults;

class FlyntTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
    public function testRegisterComponentIsForwarded()
    {
        $componentName = 'ComponentWithArea';
        $componentPath = TestHelper::getComponentsPath() . 'SingleComponent';

        $componentManagerMock = Mockery::mock('ComponentManager');

        Mockery::mock('alias:Flynt\ComponentManager')
        ->shouldReceive('getInstance')
        ->once()
        ->andReturn($componentManagerMock);

        $componentManagerMock
        ->shouldReceive('registerComponent')
        ->with($componentName, $componentPath)
        ->once();

        registerComponent($componentName, $componentPath);
    }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
    public function testRegistersComponentsFromArray()
    {
        $componentManagerMock = Mockery::mock('ComponentManager');

        Mockery::mock('alias:Flynt\ComponentManager')
        ->shouldReceive('getInstance')
        ->times(3)
        ->andReturn($componentManagerMock);

        $componentManagerMock
        ->shouldReceive('registerComponent')
        ->with('ComponentA', null)
        ->ordered()
        ->once();

        $componentManagerMock
        ->shouldReceive('registerComponent')
        ->with('ComponentB', 'some/path')
        ->ordered()
        ->once();

        $componentManagerMock
        ->shouldReceive('registerComponent')
        ->with('ComponentC', null)
        ->ordered()
        ->once();

        $componentsWithPaths = [
        'ComponentA' => null,
        'ComponentB' => 'some/path',
        'ComponentC' => null
        ];

        registerComponents($componentsWithPaths);

        $componentManagerMock
        ->shouldReceive('registerComponent')
        ->with('ComponentD', null)
        ->ordered()
        ->once();

        $componentManagerMock
        ->shouldReceive('registerComponent')
        ->with('ComponentE', null)
        ->ordered()
        ->once();

        $componentManagerMock
        ->shouldReceive('registerComponent')
        ->with('ComponentF', null)
        ->ordered()
        ->once();

        $componentsWithoutPaths = [
        'ComponentD',
        'ComponentE',
        'ComponentF'
        ];

        registerComponents($componentsWithoutPaths);

        $componentManagerMock
        ->shouldReceive('registerComponent')
        ->with('ComponentG', null)
        ->ordered()
        ->once();

        $componentManagerMock
        ->shouldReceive('registerComponent')
        ->with('ComponentH', null)
        ->ordered()
        ->once();

        $componentManagerMock
        ->shouldReceive('registerComponent')
        ->with('ComponentI', 'some/path')
        ->ordered()
        ->once();

        $componentsMixed = [
        'ComponentG',
        'ComponentH' => null,
        'ComponentI' => 'some/path'
        ];

        registerComponents($componentsMixed);
    }

  /**
   * @runInSeparateProcess
   * @preserveGlobalState disabled
   */
    public function testEchoesHtmlFromConfiguration()
    {
        $config = [
        'name' => 'SingleComponent',
        'customData' => [
        'test' => 'result'
        ]
        ];

        $constructionPlan = [
        'name' => 'SingleComponent',
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
    public function testEchoesHtmlFromConfigurationFile()
    {
        $configFileName = 'exampleConfigWithSingleComponent.json';

        $constructionPlan = [
        'name' => 'SingleComponent',
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
    public function testCallsDefaultsInitFunction()
    {
        Mockery::mock('alias:Flynt\Defaults')
        ->shouldReceive('init')
        ->once();

        initDefaults();
    }
}
