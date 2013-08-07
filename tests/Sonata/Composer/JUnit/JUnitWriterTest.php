<?php

namespace Sonata\Composer\JUnit;

use Sonata\Composer\JUnit\JUnitWriter;

class JUnitWriterTest extends \PHPUnit_Framework_TestCase
{
    public function testSave()
    {
        $suite = TestSuite::create('myname "', '/foo/bar.php', 'foo.bar', 'foo.bar');

        $testcase = TestCase::create('myname "', 2, 0.34500, 'stdClass', '/foo/bar.php', 12);
        $testcase->setError(new TestError('my "type', "</error> message ]]>"));

        $suite->addTestCase($testcase);
        $suite->addTestCase(TestCase::create('foobar', 2, 0.34500, 'stdClass', '/foo/bar.php', 25));

        $write = new JUnitWriter();
        $write->save($suite, $resource = tmpfile());

        fseek($resource, 0);
        $contents = fread($resource, 4096);
        fclose($resource);

        $xml = new \SimpleXMLElement($contents);

        $this->assertEquals(2, count($xml->xpath('//testcase')));
    }
}
