<?php

namespace WPStarter;

use PHPUnit\Framework;
use Brain\Monkey;
use Brain\Monkey\WP\Filters;
use Brain\Monkey\Functions;

class TestCase extends Framework\TestCase {

  protected function setUp() {
    parent::setUp();
    Monkey::setUpWP();

    Filters::expectApplied('WPStarter/configPath')
    ->andReturnUsing(['TestHelper', 'getConfigPath']);

    Filters::expectApplied('WPStarter/modulesPath')
    ->andReturnUsing(['TestHelper', 'getModulesPath']);

    Functions::expect('get_template_directory')
    ->andReturnUsing(['TestHelper', 'getTemplateDirectory']);
  }

  protected function tearDown() {
    Monkey::tearDownWP();
    parent::tearDown();
  }
}
