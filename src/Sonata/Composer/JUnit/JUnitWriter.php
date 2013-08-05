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

class JUnitWriter
{
    /**
     * @param TestSuite $testSuite
     * @param $file
     */
    public function save(TestSuite $testSuite, $file)
    {
        $close = false;

        if (!is_resource($file)) {
            $file = fopen($file, 'w');
            $close = true;
        }

        fwrite($file, "<?xml version=\"1.0\" ?>\n");
        fwrite($file, "<testsuites>\n");
        fwrite($file, (string) $testSuite);
        fwrite($file, "</testsuites>");

        if ($close) {
            fclose($file);
        }
    }
}