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

        $testSuite = new TestSuite((string) $xml->testsuite['name'], null, null, null);

        $this->addTestCase($testSuite, $xml);
        $this->addTestCase($testSuite, $xml->testsuite);
        $this->addTestSuite($testSuite, $xml->testsuite);

        return $testSuite;
    }

    /**
     * Add testcases to the provided TestSuite from the XML node
     *
     * @param TestSuite         $testSuite
     * @param \SimpleXMLElement $xml
     */
    public function addTestCase(TestSuite $testSuite, \SimpleXMLElement $xml)
    {
        foreach ($xml->xpath('./testcase') as $element) {
            $testcase = new TestCase(
                (string) $element['name'],
                (int)    $element['assertions'],
                (float)  $element['time'],
                (string) $element['class'],
                (string) $element['file'],
                (int)    $element['line']
            );

            if ($element->error) {
                $testcase->setError(new TestError(
                    (string) $element->error[0]->attributes()->type,
                    (string) $element->error[0]
                ));
            }

            if ($element->failure) {
                $testcase->setFailure(new TestFailure(
                    (string) $element->failure[0]->attributes()->type,
                    (string) $element->failure[0]
                ));
            }

            $testSuite->addTestCase($testcase);
        }
    }

    /**
     * Add child TestSuite to the provided TestSuite from the XML node
     *
     * @param TestSuite         $testSuite
     * @param \SimpleXMLElement $xml
     */
    public function addTestSuite(TestSuite $testSuite, \SimpleXMLElement $xml)
    {
        foreach ($xml->xpath('./testsuite') as $element) {
            $suite = new TestSuite(
                (string) $element['name'],
                (string) $element['file'],
                (string) $element['namespace'],
                (string) $element['fullPackage']
            );

            $this->addTestCase($suite, $element);
            $this->addTestSuite($suite, $element);

            $testSuite->addTestSuite($suite);
        }
    }
}