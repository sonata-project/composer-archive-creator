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

class TestCase
{
    protected $name;

    protected $class;

    protected $file;

    protected $line;

    protected $assertions = array();

    protected $time;

    protected $error;

    /**
     * @param string $name
     * @param string $class
     * @param string $file
     * @param int    $line
     * @param int    $assertions
     * @param $time  float
     */
    public function __construct($name, $assertions, $time = null, $class = null, $file = null, $line = null)
    {
        $this->name = $name;
        $this->class = $class;
        $this->file = $file;
        $this->line = $line;
        $this->assertions = $assertions;
        $this->time = $time;
    }

    /**
     * @param string $name
     * @param string $class
     * @param string $file
     * @param int    $line
     * @param string $assertions
     * @param $time  float
     *
     * @return TestCase
     */
    public static function create($name, $assertions, $time = null, $class = null, $file = null, $line = null)
    {
        return new self($name, $assertions, $time, $class, $file, $line);
    }

    /**
     * @param array $assertions
     */
    public function setAssertions($assertions)
    {
        $this->assertions = $assertions;
    }

    /**
     * @return array
     */
    public function getAssertions()
    {
        return $this->assertions;
    }

    /**
     * @param mixed $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
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
     * @param mixed $line
     */
    public function setLine($line)
    {
        $this->line = $line;
    }

    /**
     * @return mixed
     */
    public function getLine()
    {
        return $this->line;
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
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $error
     */
    public function setError(TestError $error)
    {
        $this->error = $error;
    }

    /**
     * @return TestError
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toXml();
    }

    /**
     * @return string
     */
    public function toXml()
    {
        return sprintf('<testcase name="%s" class="%s" file="%s" line="%d" assertions="%d" time="%.6f"%s',
            $this->getName(),
            $this->getClass(),
            $this->getFile(),
            $this->getLine(),
            $this->getAssertions(),
            $this->getTime(),
            $this->getError() ? sprintf(">%s</testcase>", $this->getError()) : "/>"
        );
    }
}



