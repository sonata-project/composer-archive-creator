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

use Sonata\Composer\Utils;

class TestSuite
{
    protected $name;

    protected $file;

    protected $namespace;

    protected $fullPackage;

    protected $failure;

    protected $errors;

    protected $testCases = array();

    protected $testSuites = array();

    protected $time = 0;

    protected $assertions = 0;

    /**
     * @param string $name
     * @param string $file
     * @param string $namespace
     * @param string $fullPackage
     * @param array $testCases
     */
    public function __construct($name, $file, $namespace, $fullPackage, array $testCases = array())
    {
        $this->name = $name;
        $this->file = $file;
        $this->namespace = $namespace;
        $this->fullPackage = $fullPackage;
        $this->setTestCases($testCases);
    }

    /**
     * @param string $name
     * @param string $file
     * @param string $namespace
     * @param string $fullPackage
     * @param array $testCases
     *
     * @return TestSuite
     */
    public static function create($name, $file, $namespace = null, $fullPackage = null, array $testCases = array())
    {
        return new self($name, $file, $namespace, $fullPackage, $testCases);
    }

    /**
     * @return mixed
     */
    public function getAssertions()
    {
        return $this->assertions;
    }


    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param mixed $failure
     */
    public function setFailure($failure)
    {
        $this->failure = $failure;
    }

    /**
     * @return mixed
     */
    public function getFailure()
    {
        return $this->failure;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $fullPackage
     */
    public function setFullPackage($fullPackage)
    {
        $this->fullPackage = $fullPackage;
    }

    /**
     * @return mixed
     */
    public function getFullPackage()
    {
        return $this->fullPackage;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param array $testCases
     */
    public function setTestCases($testCases)
    {
        $this->testCases = array();
        $this->time = 0;
        $this->assertions = 0;
        $this->errors = 0;

        foreach($testCases as $testCase) {
            $this->addTestCase($testCase);
        }
    }

    /**
     * @return array
     */
    public function getTestCases()
    {
        return $this->testCases;
    }

    /**
     * @param TestCase $testCase
     */
    public function addTestCase(TestCase $testCase)
    {
        $this->testCases[] = $testCase;

        $this->time       += $testCase->getTime();
        $this->assertions += $testCase->getAssertions();
        $this->errors     += $testCase->getError() ? 1 : 0;
    }

    /**
     * @param TestSuite $testSuite
     */
    public function addTestSuite(TestSuite $testSuite)
    {
        $this->testSuites[] = $testSuite;

        $this->time       += $testSuite->getTime();
        $this->assertions += $testSuite->getAssertions();
        $this->errors     += $testSuite->getErrors();
        $this->failure    += $testSuite->getFailure(); // this seems to be wrong
    }

    /**
     * @return int
     */
    public function countTests()
    {
        $total = count($this->testCases);

        foreach ($this->testSuites as $suite) {
            $total += $suite->countTests();
        }

        return $total;
    }

    /**
     * @param int $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toXml();
    }

    /**
     * @return array
     */
    public function getTestSuites()
    {
        return $this->testSuites;
    }

    /**
     * @return string
     */
    public function toXml()
    {
        $content = "";
        foreach ($this->getTestCases() as $testCase) {
            $content .= (string) $testCase->toXml();
        }

        foreach ($this->getTestSuites() as $testSuite) {
            $content .= (string) $testSuite->toXml();
        }

        return sprintf('<testsuite name="%s" tests="%d" assertions="%d" failures="%d" errors="%d" time="%.6f">%s</testsuite>',
            Utils::encodeXml($this->getName()),
            $this->countTests(),
            $this->getAssertions(),
            $this->getFailure(),
            $this->getErrors(),
            $this->getTime(),
            $content
        );
    }
}