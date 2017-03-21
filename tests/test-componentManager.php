<?php
/**
 * Class ComponentManagerTest
 *
 * @package Flynt_Core
 */

/**
 * Component Manager test case.
 */

namespace Flynt\Tests;

require_once dirname(__DIR__) . '/lib/Flynt/ComponentManager.php';

use Error;
use Brain\Monkey\WP\Filters;
use Brain\Monkey\WP\Actions;
use Flynt\Tests\TestCase;
use Flynt\Tests\TestHelper;
use Flynt\ComponentManager;

class ComponentManagerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->componentManager = ComponentManager::getInstance();
        $this->componentManager->removeAll();
    }

    public function testGetsInstance()
    {
        $this->assertInstanceOf(ComponentManager::class, ComponentManager::getInstance());
    }


    public function testPreventsCloning()
    {
        $reflection = new \ReflectionClass('\Flynt\ComponentManager');
        $cloneFn = $reflection->getMethod('__clone');
        $this->assertFalse($cloneFn->isPublic());
    }


    public function testPreventsManualInstantiation()
    {
        $reflection = new \ReflectionClass('\Flynt\ComponentManager');
        $constructor = $reflection->getConstructor();
        $this->assertFalse($constructor->isPublic());
    }

    public function testRegisterComponentUsesOptionalPathParameter()
    {
        $componentName = 'ComponentWithArea';
        $componentPath = TestHelper::getComponentsPath() . 'SingleComponent';

        Filters::expectApplied('Flynt/componentPath')
        ->with($componentPath, $componentName)
        ->once();

        $success = $this->componentManager->registerComponent($componentName, $componentPath);
        $this->assertTrue($success);
    }

    public function testComponentIsAddedToArray()
    {
        $componentName = 'SingleComponent';

        Filters::expectApplied('Flynt/componentPath')
        ->andReturnUsing(['\\Flynt\\Tests\\TestHelper', 'getComponentPath']);

        $this->componentManager->registerComponent($componentName);
        $components = $this->componentManager->getAll();
        $this->assertEquals($components, [$componentName => TestHelper::getComponentsPath() . $componentName . '/']);
    }

    public function testShowsWarningWhenComponentIsAddedMoreThanOnce()
    {
        $componentName = 'SingleComponent';
        $this->componentManager->registerComponent($componentName);

        $this->expectException('PHPUnit_Framework_Error_Warning');

        $this->componentManager->registerComponent($componentName);
    }

    public function testComponentIsOnlyAddedToArrayOnce()
    {
        $componentName = 'SingleComponent';
        $this->componentManager->registerComponent($componentName);

        Filters::expectApplied('Flynt/componentPath')
        ->never();

        $result = @$this->componentManager->registerComponent($componentName);
        $this->assertFalse($result);
    }

    public function testDoesRegisterComponentAction()
    {
        $componentName = 'SingleComponent';
        $componentPath = TestHelper::getComponentsPath() . $componentName . '/';

        Filters::expectApplied('Flynt/componentPath')
        ->andReturnUsing(['\\Flynt\\Tests\\TestHelper', 'getComponentPath']);

        Actions::expectFired('Flynt/registerComponent')
        ->with($componentName);

        Actions::expectFired("Flynt/registerComponent?name={$componentName}")
        ->with($componentName);

        $result = $this->componentManager->registerComponent($componentName);
        $this->assertTrue($result);
    }

    public function testGetsComponentFilePath()
    {
        $componentName = 'SingleComponent';

        // mock default functionality for path on registerComponent
        Filters::expectApplied('Flynt/componentPath')
        ->andReturnUsing(['\\Flynt\\Tests\\TestHelper', 'getComponentPath']);

        $this->componentManager->registerComponent($componentName);

        // test default
        $path = $this->componentManager->getComponentFilePath($componentName);
        $this->assertEquals($path, TestHelper::getComponentPath(null, $componentName) . '/index.php');

        // test second parameter
        $fileName = 'index.html';
        $path = $this->componentManager->getComponentFilePath($componentName, $fileName);
        $this->assertEquals($path, TestHelper::getComponentPath(null, $componentName) . '/' . $fileName);
    }

    public function testGetReturnsComponentPath()
    {
        $this->componentManager->registerComponent('SingleComponent', 'path');
        $path = $this->componentManager->get('SingleComponent');
        $this->assertEquals($path, 'path/');
    }

    public function testGetShowsWarningOnUnregisteredComponentParam()
    {
        $this->expectException('PHPUnit_Framework_Error_Warning');
        $path = $this->componentManager->get('SomeComponentName');
    }

    public function testGetReturnsFalseOnUnregisteredComponentParam()
    {
        $path = @$this->componentManager->get('SomeComponentName');
        $this->assertFalse($path);
    }

    public function testGetComponentFilePathReturnsFalseOnIncorrectFileName()
    {
        $componentName = 'SingleComponent';

        Filters::expectApplied('Flynt/componentPath')
        ->andReturnUsing(['\\Flynt\\Tests\\TestHelper', 'getComponentPath']);

        $this->componentManager->registerComponent($componentName);

        $path = @$this->componentManager->getComponentFilePath($componentName, 'doesNotExist.something');
        $this->assertFalse($path);
    }

    public function testGetComponentDirPathReturnsCorrectPath()
    {
        // mock default functionality for path on registerComponent
        Filters::expectApplied('Flynt/componentPath')
        ->andReturnUsing(['\\Flynt\\Tests\\TestHelper', 'getComponentPath']);

        $this->componentManager->registerComponent('SingleComponent');
        $path = $this->componentManager->getComponentDirPath('SingleComponent');

        // Could also check the string to be the same, but this is nice and short
        $this->assertFileExists($path);
    }

    public function testGetComponentDirPathReturnsFalseForIncorrectDir()
    {
        $this->componentManager->registerComponent('SingleComponent', 'path');
        $result = $this->componentManager->getComponentDirPath('SingleComponent');
        $this->assertFalse($result);
    }

    public function testReturnsComponentList()
    {
        $componentA = 'SingleComponent';
        $componentB = 'ComponentWithArea';

        Filters::expectApplied('Flynt/componentPath')
        ->andReturnUsing(['\\Flynt\\Tests\\TestHelper', 'getComponentPath']);

        $this->componentManager->registerComponent($componentA);
        $this->assertEquals($this->componentManager->getAll(), [
        'SingleComponent' => TestHelper::getComponentsPath() . $componentA . '/'
        ]);

        $this->componentManager->registerComponent($componentB);
        $this->assertEquals($this->componentManager->getAll(), [
        'SingleComponent' => TestHelper::getComponentsPath() . $componentA . '/',
        'ComponentWithArea' => TestHelper::getComponentsPath() . $componentB . '/'
        ]);
    }

    public function testRemovesComponent()
    {
        $component = 'SingleComponent';
        $anotherComponent = 'AnotherComponent';

        Filters::expectApplied('Flynt/componentPath')
        ->andReturnUsing(['\\Flynt\\Tests\\TestHelper', 'getComponentPath']);

        $this->componentManager->registerComponent($component);
        $this->componentManager->registerComponent($anotherComponent);

        $this->componentManager->remove($component);

        $this->assertFalse(@$this->componentManager->get($component));
        $this->assertArrayHasKey($anotherComponent, $this->componentManager->getAll());
    }

    public function testClearsComponentList()
    {
        $component = 'SingleComponent';

        Filters::expectApplied('Flynt/componentPath')
        ->andReturnUsing(['\\Flynt\\Tests\\TestHelper', 'getComponentPath']);

        $this->componentManager->registerComponent($component);
        $this->assertEquals($this->componentManager->getAll(), [
        'SingleComponent' => TestHelper::getComponentsPath() . $component . '/'
        ]);

        $this->componentManager->removeAll();
        $this->assertEquals($this->componentManager->getAll(), []);
    }

    public function testComponentIsRegistered()
    {
        $component = 'SingleComponent';

        Filters::expectApplied('Flynt/componentPath')
        ->andReturnUsing(['\\Flynt\\Tests\\TestHelper', 'getComponentPath']);

        $this->componentManager->registerComponent($component);

        $this->assertTrue($this->componentManager->isRegistered($component));
        $this->assertFalse($this->componentManager->isRegistered('test'));
    }
}
