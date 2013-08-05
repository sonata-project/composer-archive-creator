<?php

namespace Sonata\Composer\JUnit;

use Sonata\Composer\JUnit\TestError;

class TestErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testStaticMethod()
    {
        $error = TestError::create('myname', 'mymesssage');

        $expected = '<error type="myname">mymesssage</error>';

        $this->assertEquals($expected, $error->toXml());
    }
}
