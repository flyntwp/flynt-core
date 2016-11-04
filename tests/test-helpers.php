<?php
/**
 * Class ConstructionPlanTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Construction plan test case.
 */

use WPStarter\TestCase;
use function WPStarter\Helpers\extractNestedDataFromArray;

class HelpersTest extends TestCase {

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
