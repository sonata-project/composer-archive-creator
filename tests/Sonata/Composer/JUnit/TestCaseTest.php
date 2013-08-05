<?php

namespace Sonata\Composer\JUnit;

use Sonata\Composer\JUnit\TestCase;

class TestCaseTest extends \PHPUnit_Framework_TestCase
{
    public function testStaticMethod()
    {
        $message = TestCase::create('myname', 2, 0.345, 'stdClass', '/foo/bar.php', 12);

        $expected = '<testcase name="myname" class="stdClass" file="/foo/bar.php" line="12" assertions="2" time="0.345000"/>';

        $this->assertEquals($expected, $message->toXml());
    }
}
