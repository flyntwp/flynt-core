<?php

/**
 * Class WPStarterTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Construction plan test case.
 */

use WPStarter\TestCase;
use WPStarter\WPStarter;
use Brain\Monkey\WP\Filters;

class WPStarterTest extends TestCase {
  protected function setUp() {
    parent::setUp();

    // reset private static modules array in WPStarter
    $reflectedClass = new \ReflectionClass(WPStarter::class);
    $reflectedProperty = $reflectedClass->getProperty('modules');
    $reflectedProperty->setAccessible(true);
    $reflectedProperty = $reflectedProperty->setValue([]);
  }

  protected function tearDown() {
    parent::tearDown();
  }

  public function testLoadsFunctionsPhpOnRegisterModule() {
    $moduleName = 'SingleModule';

    Filters::expectAdded("WPStarter/DataFilters/{$moduleName}/foo")
    ->once();

    WPStarter::registerModule($moduleName);
  }

  public function testThrowsErrorWhenModuleFolderNotFound() {
    $this->expectException(Exception::class);
    WPStarter::registerModule('NotARealModule');
  }

  public function testModuleIsAddedToArray() {
    $moduleName = 'SingleModule';
    WPStarter::registerModule($moduleName);

    $modules = WPStarter::getModuleList();
    $this->assertEquals($modules, [$moduleName => TestHelper::getModulesPath() . $moduleName]);
  }

  public function testModuleIsOnlyAddedToArrayOnce() {
    $moduleName = 'SingleModule';
    WPStarter::registerModule($moduleName);

    $this->expectException(Exception::class);
    WPStarter::registerModule($moduleName);
  }

  public function testReturnsModuleList() {
    $moduleA = 'SingleModule';
    $moduleB = 'ModuleWithArea';

    WPStarter::registerModule($moduleA);
    $this->assertEquals(WPStarter::getModuleList(), [
      'SingleModule' => TestHelper::getModulesPath() . $moduleA
    ]);

    WPStarter::registerModule($moduleB);
    $this->assertEquals(WPStarter::getModuleList(), [
      'SingleModule' => TestHelper::getModulesPath() . $moduleA,
      'ModuleWithArea' => TestHelper::getModulesPath() . $moduleB
    ]);
  }

  public function testEchoesHtmlFromConfiguration() {
    $this->expectOutputString("<div>SingleModule result</div>\n");
    WPStarter::echoHtmlFromConfig([
      'name' => 'SingleModule',
      'customData' => [
        'test' => 'result'
      ]
    ]);
  }

  public function testEchoesHtmlFromConfigurationFile() {
    $this->expectOutputString("<div>SingleModule result</div>\n");
    WPStarter::echoHtmlFromConfigFile('exampleConfigWithSingleModule.json');
  }

  public function testGetsHtmlFromConfiguration() {
    $html = WPStarter::getHtmlFromConfig([
      'name' => 'SingleModule',
      'customData' => [
        'test' => 'result'
      ]
    ]);
    $this->assertEquals($html, "<div>SingleModule result</div>\n");
  }

  public function testGetsHtmlFromConfigurationFile() {
    $html = WPStarter::getHtmlFromConfigFile('exampleConfigWithSingleModule.json');
    $this->assertEquals($html, "<div>SingleModule result</div>\n");
  }
}
