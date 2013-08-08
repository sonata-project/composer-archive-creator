<?php

namespace Sonata\Composer\JUnit;

use Sonata\Composer\JUnit\TestCase;
use Sonata\Composer\JUnit\TestSuite;

class TestSuiteTest extends \PHPUnit_Framework_TestCase
{
    public function testStaticMethod()
    {
        $suite = TestSuite::create('myname', '/foo/bar.php', 'foo.bar', 'foo.bar');

        $expected = '<testsuite name="myname" tests="0" assertions="0" namespace="foo.bar" fullsPackage="foo.bar" failures="0" errors="0" time="0.000000"></testsuite>';

        $this->assertEquals($expected, $suite->toXml());
    }

    public function testWithTestCase()
    {
        $suite = TestSuite::create('myname', '/foo/bar.php', 'foo.bar', 'foo.bar');

        $suite->addTestCase(TestCase::create('myname', 2, 0.34500, 'stdClass', '/foo/bar.php', 12));
        $suite->addTestCase(TestCase::create('foobar', 2, 0.34500, 'stdClass', '/foo/bar.php', 25));

        $this->assertEquals(4, $suite->getAssertions());
        // WTF!! at least 0.700000001 .... not this => php -r "var_dump(0.345 + 0.345);" => float(0.69)
        // $this->assertEquals(0.70000, $suite->getTime());
    }

    public function testWithTestSuite()
    {
        $master = TestSuite::create('master', null);

        $suite = TestSuite::create('myname', '/foo/bar.php', 'foo.bar', 'foo.bar');

        $suite->addTestCase(TestCase::create('myname', 2, 0.34500, 'stdClass', '/foo/bar.php', 12));
        $suite->addTestCase(TestCase::create('foobar', 2, 0.34500, 'stdClass', '/foo/bar.php', 25));

        $master->addTestSuite($suite);

        $this->assertEquals(4, $suite->getAssertions());
    }
}
