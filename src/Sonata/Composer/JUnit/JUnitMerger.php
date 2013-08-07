<?php

/*
* This file is part of the Sonata project.
*
* (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sonata\Composer\JUnit;

class JUnitMerger
{
    /**
     * @param \Iterator $files
     * @param TestSuite $testSuite
     *
     * @return TestSuite
     */
    public function merge(\Iterator $files, TestSuite $testSuite = null)
    {
        if (!$testSuite) {
            $testSuite = new TestSuite(null, null, null, null);
        }

        $loader = new PHPUnitLoader();

        foreach($files as $file) {
            $suite = $loader->load($file->getContents());

            if (!$suite) {
                continue;
            }

            $testSuite->addTestSuite($suite);
        }

        return $testSuite;
    }
}