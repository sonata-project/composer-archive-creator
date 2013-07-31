<?php

/*
* This file is part of the Sonata project.
*
* (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sonata\Composer\Console;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;

class DateOutputFormatter implements OutputFormatterInterface
{
    protected $formatter;

    /**
     * @param OutputFormatterInterface $formatter
     */
    public function __construct(OutputFormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated($decorated)
    {
        $this->formatter->setDecorated($decorated);
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorated()
    {
        return $this->formatter->isDecorated();
    }

    /**
     * {@inheritdoc}
     */
    public function setStyle($name, OutputFormatterStyleInterface $style)
    {
        $this->formatter->setStyle($name, $style);
    }

    /**
     * {@inheritdoc}
     */
    public function hasStyle($name)
    {
        return $this->formatter->hasStyle($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getStyle($name)
    {
        return $this->formatter->getStyle($name);
    }

    /**
     * {@inheritdoc}
     */
    public function format($message)
    {
        $date = new \DateTime();

        return $this->formatter->format($date->format("[Y-m-d G:i:s] ") . $message);
    }
}