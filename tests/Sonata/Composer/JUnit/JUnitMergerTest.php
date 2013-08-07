<?php

namespace Sonata\Composer\JUnit;

use Sonata\Composer\JUnit\JUnitMerger;
use Symfony\Component\Finder\Finder;

class JUnitMergerTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidXmlFile()
    {
        $f = Finder::create()
            ->ignoreVCS(true)
            ->in(__DIR__.'/../../../fixtures/')
            ->name('*.xml');

        $merger = new JUnitMerger();

        $testsuite = $merger->merge($f->getIterator());

        $this->assertNotNull($testsuite);
        $this->assertEquals(1, $testsuite->getErrors());
        $this->assertEquals(6, $testsuite->countTests());
    }
}
