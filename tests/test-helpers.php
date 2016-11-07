<?php
/**
 * Class ConstructionPlanTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Construction plan test case.
 */

require_once dirname(__DIR__) . '/lib/WPStarter/Helpers.php';

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
    $value = extractNestedDataFromArray(null);
    $this->assertEquals($value, '');
    $value = extractNestedDataFromArray('string');
    $this->assertEquals($value, '');
    $value = extractNestedDataFromArray('stringA', 'stringB');
    $this->assertEquals($value, '');
    $value = extractNestedDataFromArray('stringA', null, 'stringB');
    $this->assertEquals($value, '');
    $value = extractNestedDataFromArray([$arr, 'boo']);
    $this->assertEquals($value, '');
    $value = extractNestedDataFromArray([$arr, 'foo']);
    $this->assertEquals($value, $foo);
    $value = extractNestedDataFromArray([$arr, 'foo', 'bar']);
    $this->assertEquals($value, $bar);
  }

}
