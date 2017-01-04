<?php
/**
 * Class ComponentManagerTest
 *
 * @package Flynt_Core
 */

/**
 * Component Manager test case.
 */

require_once dirname(__DIR__) . '/lib/Flynt/ComponentManager.php';

use Flynt\TestCase;
use Flynt\ComponentManager;
use Brain\Monkey\WP\Filters;
use Brain\Monkey\WP\Actions;

class ComponentManagerTest extends TestCase {
  function setUp() {
    parent::setUp();

    $this->componentManager = ComponentManager::getInstance();
    $this->componentManager->removeAll();
  }

  function testGetsInstance() {
    $this->assertInstanceOf(ComponentManager::class, ComponentManager::getInstance());
  }

  function testPreventsCloning() {
    $this->expectException(Error::class);
    clone($this->componentManager);
  }

  function testPreventsManualInstantiation() {
    $this->expectException(Error::class);
    new ComponentManager();
  }

  function testRegisterComponentUsesOptionalPathParameter() {
    $componentName = 'ComponentWithArea';
    $componentPath = TestHelper::getComponentsPath() . 'SingleComponent';

    Filters::expectApplied('Flynt/componentPath')
    ->with($componentPath, $componentName)
    ->once();

    $success = $this->componentManager->registerComponent($componentName, $componentPath);
    $this->assertTrue($success);
  }

  function testComponentIsAddedToArray() {
    $componentName = 'SingleComponent';

    Filters::expectApplied('Flynt/componentPath')
    ->andReturnUsing(['TestHelper', 'getComponentPath']);

    $this->componentManager->registerComponent($componentName);
    $components = $this->componentManager->getAll();
    $this->assertEquals($components, [$componentName => TestHelper::getComponentsPath() . $componentName . '/']);
  }

  function testShowsWarningWhenComponentIsAddedMoreThanOnce() {
    $componentName = 'SingleComponent';
    $this->componentManager->registerComponent($componentName);

    $this->expectException('PHPUnit_Framework_Error_Warning');

    $this->componentManager->registerComponent($componentName);
  }

  function testComponentIsOnlyAddedToArrayOnce() {
    $componentName = 'SingleComponent';
    $this->componentManager->registerComponent($componentName);

    Filters::expectApplied('Flynt/componentPath')
    ->never();

    $result = @$this->componentManager->registerComponent($componentName);
    $this->assertFalse($result);
  }

  function testDoesRegisterComponentAction() {
    $componentName = 'SingleComponent';
    $componentPath = TestHelper::getComponentsPath() . $componentName . '/';

    Filters::expectApplied('Flynt/componentPath')
    ->andReturnUsing(['TestHelper', 'getComponentPath']);

    Actions::expectFired('Flynt/registerComponent')
    ->with($componentName);

    Actions::expectFired("Flynt/registerComponent?name={$componentName}")
    ->with($componentName);

    $result = $this->componentManager->registerComponent($componentName);
    $this->assertTrue($result);
  }

  function testGetsComponentFilePath() {
    $componentName = 'SingleComponent';

    // mock default functionality for path on registerComponent
    Filters::expectApplied('Flynt/componentPath')
    ->andReturnUsing(['TestHelper', 'getComponentPath']);

    $this->componentManager->registerComponent($componentName);

    // test default
    $path = $this->componentManager->getComponentFilePath($componentName);
    $this->assertEquals($path, TestHelper::getComponentPath(null, $componentName) . '/index.php');

    // test second parameter
    $fileName = 'index.html';
    $path = $this->componentManager->getComponentFilePath($componentName, $fileName);
    $this->assertEquals($path, TestHelper::getComponentPath(null, $componentName) . '/' . $fileName);
  }

  function testGetReturnsComponentPath() {
    $this->componentManager->registerComponent('SingleComponent', 'path');
    $path = $this->componentManager->get('SingleComponent');
    $this->assertEquals($path, 'path/');
  }

  function testGetShowsWarningOnUnregisteredComponentParam() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    $path = $this->componentManager->get('SomeComponentName');
  }

  function testGetReturnsFalseOnUnregisteredComponentParam() {
    $path = @$this->componentManager->get('SomeComponentName');
    $this->assertFalse($path);
  }

  function testGetComponentFilePathReturnsFalseOnIncorrectFileName() {
    $componentName = 'SingleComponent';

    Filters::expectApplied('Flynt/componentPath')
    ->andReturnUsing(['TestHelper', 'getComponentPath']);

    $this->componentManager->registerComponent($componentName);

    $path = @$this->componentManager->getComponentFilePath($componentName, 'doesNotExist.something');
    $this->assertFalse($path);
  }

  function testGetComponentDirPathReturnsCorrectPath() {
    // mock default functionality for path on registerComponent
    Filters::expectApplied('Flynt/componentPath')
    ->andReturnUsing(['TestHelper', 'getComponentPath']);

    $this->componentManager->registerComponent('SingleComponent');
    $path = $this->componentManager->getComponentDirPath('SingleComponent');

    // Could also check the string to be the same, but this is nice and short
    $this->assertFileExists($path);
  }

  function testGetComponentDirPathReturnsFalseForIncorrectDir() {
    $this->componentManager->registerComponent('SingleComponent', 'path');
    $result = $this->componentManager->getComponentDirPath('SingleComponent');
    $this->assertFalse($result);
  }

  function testReturnsComponentList() {
    $componentA = 'SingleComponent';
    $componentB = 'ComponentWithArea';

    Filters::expectApplied('Flynt/componentPath')
    ->andReturnUsing(['TestHelper', 'getComponentPath']);

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

  function testRemovesComponent() {
    $component = 'SingleComponent';
    $anotherComponent = 'AnotherComponent';

    Filters::expectApplied('Flynt/componentPath')
    ->andReturnUsing(['TestHelper', 'getComponentPath']);

    $this->componentManager->registerComponent($component);
    $this->componentManager->registerComponent($anotherComponent);

    $this->componentManager->remove($component);

    $this->assertFalse(@$this->componentManager->get($component));
    $this->assertArrayHasKey($anotherComponent, $this->componentManager->getAll());
  }

  function testClearsComponentList() {
    $component = 'SingleComponent';

    Filters::expectApplied('Flynt/componentPath')
    ->andReturnUsing(['TestHelper', 'getComponentPath']);

    $this->componentManager->registerComponent($component);
    $this->assertEquals($this->componentManager->getAll(), [
      'SingleComponent' => TestHelper::getComponentsPath() . $component . '/'
    ]);

    $this->componentManager->removeAll();
    $this->assertEquals($this->componentManager->getAll(), []);
  }

  function testComponentIsRegistered() {
    $component = 'SingleComponent';

    Filters::expectApplied('Flynt/componentPath')
    ->andReturnUsing(['TestHelper', 'getComponentPath']);

    $this->componentManager->registerComponent($component);

    $this->assertTrue($this->componentManager->isRegistered($component));
    $this->assertFalse($this->componentManager->isRegistered('test'));
  }
}
