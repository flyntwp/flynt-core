<?php
/**
 * Class ConstructionPlanTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Construction plan test case.
 */

use PHPUnit\Framework\TestCase;
use function WPStarter\Helpers\extractNestedDataFromArray;

class HelpersTest extends TestCase {
  function setUp() {
    WP_Mock::setUp();
  }

  function tearDown() {
    WP_Mock::tearDown();
  }

  function testExtractNestedDataFromArray() {
    $bar = 'baz';
    $foo = [
      'bar' => $bar
    ];
    $arr = [
      'foo' => $foo
    ];
    $value = extractNestedDataFromArray([$arr, 'foo']);
    $this->assertEquals($value, $foo);
    $value = extractNestedDataFromArray([$arr, 'foo', 'bar']);
    $this->assertEquals($value, $bar);
  }
}
