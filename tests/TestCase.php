<?php

namespace WPStarter;

use PHPUnit\Framework;
use Brain\Monkey;
use Brain\Monkey\WP\Filters;

class TestCase extends Framework\TestCase {

  protected function setUp() {
    parent::setUp();
    Monkey::setUpWP();

    Filters::expectApplied('WPStarter/configPath')
    ->andReturnUsing(['TestHelper', 'getConfigPath']);

    Filters::expectApplied('WPStarter/defaultModulesPath')
    ->andReturnUsing(['TestHelper', 'getModulesPath']);
  }

  protected function tearDown() {
    Monkey::tearDownWP();
    parent::tearDown();
  }
}
