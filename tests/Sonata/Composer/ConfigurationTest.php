<?php

namespace Sonata\Composer\Reporter;

use Sonata\Composer\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testValidConfiguration()
    {
        $config = new Configuration();

        $process = new Processor();

        $config = $process->processConfiguration($config, array(array(
            'git' => 'git'
        )));

        $this->assertEquals('git', $config['git']);
    }
}
