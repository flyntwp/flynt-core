<?php
/**
 * Class HelpersTest
 *
 * @package Wp_Starter_Plugin
 */

/**
 * Helpers test case.
 */

require_once dirname(__DIR__) . '/lib/WPStarter/Helpers.php';

use WPStarter\TestCase;
use WPStarter\Helpers;

class HelpersTest extends TestCase {

  function testExtractNestedDataFromArray() {
    $bar = 'baz';
    $foo = [
      'bar' => $bar
    ];
    $arr = [
      'foo' => $foo
    ];
    $value = Helpers::extractNestedDataFromArray(null);
    $this->assertEquals($value, '');
    $value = Helpers::extractNestedDataFromArray('string');
    $this->assertEquals($value, '');
    $value = Helpers::extractNestedDataFromArray('stringA', 'stringB');
    $this->assertEquals($value, '');
    $value = Helpers::extractNestedDataFromArray('stringA', null, 'stringB');
    $this->assertEquals($value, '');
    $value = Helpers::extractNestedDataFromArray([$arr, 'boo']);
    $this->assertEquals($value, '');
    $value = Helpers::extractNestedDataFromArray([$arr, 'foo']);
    $this->assertEquals($value, $foo);
    $value = Helpers::extractNestedDataFromArray([$arr, 'foo', 'bar']);
    $this->assertEquals($value, $bar);
  }

}
