<?php

/*
* This file is part of the Sonata project.
*
* (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sonata\Composer\Tester;

use Sonata\Composer\JUnit\TestCase;
use Sonata\Composer\JUnit\TestFailure;
use Symfony\Component\Process\Process;

class ConsoleTester
{
    /**
     * @param Process $process
     *
     * @return TestCase
     */
    public function test(Process $process)
    {
        $process->run();

        $testcase = new TestCase($process->getCommandLine(), 0);

        $testcase->incAssertions();

        if ($process->getExitCode() != 0) {
            $testcase->setFailure(new TestFailure('exec', $process->getOutput()));

            return $testcase;
        }

        return $testcase;
    }
}