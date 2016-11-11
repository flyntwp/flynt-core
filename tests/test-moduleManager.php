<?php
/**
 * Class ModuleManagerTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Module Manager test case.
 */

require_once dirname(__DIR__) . '/lib/WPStarter/ModuleManager.php';

use WPStarter\TestCase;
use WPStarter\ModuleManager;
use Brain\Monkey\WP\Filters;
use Brain\Monkey\WP\Actions;

class ModuleManagerTest extends TestCase {
  function setUp() {
    parent::setUp();

    $this->moduleManager = ModuleManager::getInstance();
    $this->moduleManager->removeAll();
  }

  function testGetsInstance() {
    $this->assertInstanceOf(ModuleManager::class, ModuleManager::getInstance());
  }

  function testPreventsCloning() {
    $this->expectException(Error::class);
    clone($this->moduleManager);
  }

  function testPreventsManualInstantiation() {
    $this->expectException(Error::class);
    new ModuleManager();
  }

  function testRegisterModuleUsesOptionalPathParameter() {
    $moduleName = 'ModuleWithArea';
    $modulePath = TestHelper::getModulesPath() . 'SingleModule';

    Filters::expectApplied('WPStarter/modulePath')
    ->with($modulePath, $moduleName)
    ->once();

    $success = $this->moduleManager->registerModule($moduleName, $modulePath);
    $this->assertTrue($success);
  }

  function testModuleIsAddedToArray() {
    $moduleName = 'SingleModule';

    Filters::expectApplied('WPStarter/modulePath')
    ->andReturnUsing(['TestHelper', 'getModulePath']);

    $this->moduleManager->registerModule($moduleName);
    $modules = $this->moduleManager->getAll();
    $this->assertEquals($modules, [$moduleName => TestHelper::getModulesPath() . $moduleName . '/']);
  }

  function testShowsWarningWhenModuleIsAddedMoreThanOnce() {
    $moduleName = 'SingleModule';
    $this->moduleManager->registerModule($moduleName);

    $this->expectException('PHPUnit_Framework_Error_Warning');

    $this->moduleManager->registerModule($moduleName);
  }

  function testModuleIsOnlyAddedToArrayOnce() {
    $moduleName = 'SingleModule';
    $this->moduleManager->registerModule($moduleName);

    Filters::expectApplied('WPStarter/modulePath')
    ->never();

    @$this->moduleManager->registerModule($moduleName);
  }

  function testDoesRegisterModuleAction() {
    $moduleName = 'SingleModule';
    $modulePath = TestHelper::getModulesPath() . $moduleName . '/';

    Filters::expectApplied('WPStarter/modulePath')
    ->andReturnUsing(['TestHelper', 'getModulePath']);

    Actions::expectFired('WPStarter/registerModule')
    ->with($modulePath, $moduleName);

    Actions::expectFired("WPStarter/registerModule?name={$moduleName}")
    ->with($modulePath);

    $this->moduleManager->registerModule($moduleName);
  }

  function testGetsModuleFilePath() {
    $moduleName = 'SingleModule';

    // mock default functionality for path on registerModule
    Filters::expectApplied('WPStarter/modulePath')
    ->andReturnUsing(['TestHelper', 'getModulePath']);

    $this->moduleManager->registerModule($moduleName);

    // test default
    $path = $this->moduleManager->getModuleFilePath($moduleName);
    $this->assertEquals($path, TestHelper::getModulePath(null, $moduleName) . '/index.php');

    // test second parameter
    $fileName = 'index.html';
    $path = $this->moduleManager->getModuleFilePath($moduleName, $fileName);
    $this->assertEquals($path, TestHelper::getModulePath(null, $moduleName) . '/' . $fileName);
  }

  function testGetModuleFilePathShowsWarningOnUnregisteredModuleParam() {
    $this->expectException('PHPUnit_Framework_Error_Warning');
    $path = $this->moduleManager->getModuleFilePath('SomeModuleName');
  }

  function testGetModuleFilePathReturnsFalseOnUnregisteredModuleParam() {
    $path = @$this->moduleManager->getModuleFilePath('SomeModuleName');
    $this->assertFalse($path);
  }

  function testGetModuleFilePathShowsWarningOnIncorrectFileName() {
    $moduleName = 'SingleModule';

    Filters::expectApplied('WPStarter/modulePath')
    ->andReturnUsing(['TestHelper', 'getModulePath']);

    $this->moduleManager->registerModule($moduleName);

    $this->expectException('PHPUnit_Framework_Error_Warning');
    $path = $this->moduleManager->getModuleFilePath($moduleName, 'doesNotExist.something');
  }

  function testGetModuleFilePathReturnsFalseOnIncorrectFileName() {
    $moduleName = 'SingleModule';

    Filters::expectApplied('WPStarter/modulePath')
    ->andReturnUsing(['TestHelper', 'getModulePath']);

    $this->moduleManager->registerModule($moduleName);

    $path = @$this->moduleManager->getModuleFilePath($moduleName, 'doesNotExist.something');
    $this->assertFalse($path);
  }

  function testReturnsModuleList() {
    $moduleA = 'SingleModule';
    $moduleB = 'ModuleWithArea';

    Filters::expectApplied('WPStarter/modulePath')
    ->andReturnUsing(['TestHelper', 'getModulePath']);

    $this->moduleManager->registerModule($moduleA);
    $this->assertEquals($this->moduleManager->getAll(), [
      'SingleModule' => TestHelper::getModulesPath() . $moduleA . '/'
    ]);

    $this->moduleManager->registerModule($moduleB);
    $this->assertEquals($this->moduleManager->getAll(), [
      'SingleModule' => TestHelper::getModulesPath() . $moduleA . '/',
      'ModuleWithArea' => TestHelper::getModulesPath() . $moduleB . '/'
    ]);
  }

  function testClearsModuleList() {
    $module = 'SingleModule';

    Filters::expectApplied('WPStarter/modulePath')
    ->andReturnUsing(['TestHelper', 'getModulePath']);

    $this->moduleManager->registerModule($module);
    $this->assertEquals($this->moduleManager->getAll(), [
      'SingleModule' => TestHelper::getModulesPath() . $module . '/'
    ]);

    $this->moduleManager->removeAll();
    $this->assertEquals($this->moduleManager->getAll(), []);
  }
}
