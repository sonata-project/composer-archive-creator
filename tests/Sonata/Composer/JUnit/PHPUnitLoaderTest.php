<?php

namespace Sonata\Composer\JUnit;

use Sonata\Composer\JUnit\PHPUnitLoader;
use Symfony\Component\Finder\SplFileInfo;

class PHPUnitLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidXmlFile()
    {
        $loader = new PHPUnitLoader();
        $suite = $loader->load(file_get_contents(__DIR__.'/../../../fixtures/invalid-xml.xml'));

        $this->assertNull($suite);
    }

    public function testValidXmlFile()
    {
        $loader = new PHPUnitLoader();
        $suite = $loader->load(file_get_contents(__DIR__.'/../../../fixtures/phpunit-junit.xml'));

        $this->assertEquals(1, $suite->getErrors());
        $this->assertEquals(6, $suite->countTests());
    }
}
