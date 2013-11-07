<?php

namespace Sonata\Composer\Tester;

use Sonata\Composer\Tester\ConsoleTester;
use Symfony\Component\Process\Process;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testExit0()
    {
        $tester = new ConsoleTester();

        $testcase = $tester->test(new Process('exit 0'));

        $this->assertNotNull($testcase);
        $this->assertNull($testcase->getError());
        $this->assertNull($testcase->getFailure());
        $this->assertEquals(1, $testcase->getAssertions());
    }

    public function testExit1()
    {
        $tester = new ConsoleTester();

        $testcase = $tester->test(new Process('exit 1'));

        $this->assertNotNull($testcase);
        $this->assertNull($testcase->getError());
        $this->assertNotNull($testcase->getFailure());
        $this->assertEquals(1, $testcase->getAssertions());
    }
}
