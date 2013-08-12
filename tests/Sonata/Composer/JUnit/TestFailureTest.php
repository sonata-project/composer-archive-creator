<?php

namespace Sonata\Composer\JUnit;

use Sonata\Composer\JUnit\TestFailure;

class TestFailureTest extends \PHPUnit_Framework_TestCase
{
    public function testStaticMethod()
    {
        $error = TestFailure::create('myname', 'mymesssage');

        $expected = '<failure type="myname">mymesssage</failure>';

        $this->assertEquals($expected, $error->toXml());
    }
}
