<?php

namespace Flynt\Tests;

use PHPUnit\Framework;
use Brain\Monkey;
use Brain\Monkey\Functions;

class TestCase extends Framework\TestCase
{

    protected function setUp()
    {
        parent::setUp();
        Monkey::setUpWP();

        Functions::expect('get_template_directory')
        ->andReturnUsing(['\\Flynt\\Tests\\TestHelper', 'getTemplateDirectory']);

        Functions::expect('trailingslashit')
        ->andReturnUsing(['\\Flynt\\Tests\\TestHelper', 'trailingSlashIt']);
    }

    protected function tearDown()
    {
        Monkey::tearDownWP();
        parent::tearDown();
    }
}
