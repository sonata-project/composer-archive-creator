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

class PHPUnitLoader
{
    /**
     * @param string $content
     *
     * @return TestSuite
     */
    public function load($content)
    {
        try {
            $xml = new \SimpleXMLElement($content);
        } catch (\Exception $e) {
            return null;
        }

        $testsuite = new TestSuite(
            (string) $xml->testsuite[0]['name'],
            null,
            null,
            null,
            (int) $xml->testsuite[0]['errors']
        );

        foreach ($xml->xpath('//testcase') as $element) {
            $testcase = new TestCase(
                (string) $element['name'],
                (int)    $element['assertions'],
                (float)  $element['time'],
                (string) $element['class'],
                (string) $element['file'],
                (int)    $element['line']
            );

            if ($element->error[0]) {
                $testcase->setError(new TestError(
                    (string) $element->error[0]->attributes()->type,
                    (string) $element->error[0]
                ));
            }

            $testsuite->addTestCase($testcase);
        }

        return $testsuite;
    }
}