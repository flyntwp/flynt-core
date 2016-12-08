<?php

namespace Flynt;

use PHPUnit\Framework;
use Brain\Monkey;
use Brain\Monkey\Functions;

class TestCase extends Framework\TestCase {

  protected function setUp() {
    parent::setUp();
    Monkey::setUpWP();

    Functions::expect('get_template_directory')
    ->andReturnUsing(['TestHelper', 'getTemplateDirectory']);

    Functions::expect('trailingslashit')
    ->andReturnUsing(['TestHelper', 'trailingSlashIt']);
  }

  protected function tearDown() {
    Monkey::tearDownWP();
    parent::tearDown();
  }
}
