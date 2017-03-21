<?php
/**
 * Class HelpersTest
 *
 * @package Flynt_Core
 */

/**
 * Helpers test case.
 */

namespace Flynt\Tests;

require_once dirname(__DIR__) . '/lib/Flynt/Helpers.php';

use Flynt\Tests\TestCase;
use Flynt\Helpers;

class HelpersTest extends TestCase
{

    public function testExtractNestedDataFromArray()
    {
        $bar = 'baz';
        $foo = [
        'bar' => $bar
        ];
        $arr = [
        'foo' => $foo
        ];
        $fooObj = (object) $foo;
        $obj = (object) [
        'foo' => $fooObj
        ];
        $value = Helpers::extractNestedDataFromArray([null]);
        $this->assertEquals($value, '');
        $value = Helpers::extractNestedDataFromArray(['string']);
        $this->assertEquals($value, 'string');
        $value = Helpers::extractNestedDataFromArray(['stringA', 'stringB']);
        $this->assertEquals($value, '');
        $value = Helpers::extractNestedDataFromArray(['stringA', null, 'stringB']);
        $this->assertEquals($value, '');
        $value = Helpers::extractNestedDataFromArray([$arr, 'boo']);
        $this->assertEquals($value, '');
        $value = Helpers::extractNestedDataFromArray([$arr, 'foo']);
        $this->assertEquals($value, $foo);
        $value = Helpers::extractNestedDataFromArray([$arr, 'foo', 'bar']);
        $this->assertEquals($value, $bar);
        $value = Helpers::extractNestedDataFromArray([[], $arr, 'foo']);
        $this->assertEquals($value, $foo);
        $value = Helpers::extractNestedDataFromArray([$arr]);
        $this->assertEquals($value, $arr);
        $value = Helpers::extractNestedDataFromArray([$arr, $foo]);
        $this->assertEquals($value, $foo);
        $value = Helpers::extractNestedDataFromArray([$obj, 'foo']);
        $this->assertEquals($value, $fooObj);
        $value = Helpers::extractNestedDataFromArray([$obj, 'foo', 'bar']);
        $this->assertEquals($value, $bar);
    }
}
