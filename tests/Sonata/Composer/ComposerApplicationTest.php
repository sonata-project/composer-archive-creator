<?php

namespace Sonata\Composer\Reporter;

use Sonata\Composer\ComposerApplication;

class ComposerApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testValidConfiguration()
    {
        $application = new ComposerApplication();

        $this->assertEquals('hello', $application->getConfiguration('foo', 'hello'));

        $this->assertCount(0, $application->getReporters()); // by default no reporters should be enabled
    }
}
