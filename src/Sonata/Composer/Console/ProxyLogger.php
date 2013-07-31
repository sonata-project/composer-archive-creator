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
use Symfony\Component\Console\Output\OutputInterface;

class ProxyLogger implements OutputInterface
{
    protected $outputs;

    /**
     * @param array $outputs
     */
    public function __construct(array $outputs)
    {
        $this->outputs = $outputs;
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        foreach($this->outputs as $output) {
            $output->write($messages, $newline, $type);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        foreach($this->outputs as $output) {
            $output->writeln($messages, $type);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setVerbosity($level)
    {
        foreach($this->outputs as $output) {
            $output->setVerbosity($level);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getVerbosity()
    {
        foreach($this->outputs as $output) {
            return $output->getVerbosity();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated($decorated)
    {
        foreach($this->outputs as $output) {
            $output->setDecorated($decorated);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorated()
    {
        foreach($this->outputs as $output) {
            return $output->isDecorated();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        foreach($this->outputs as $output) {
            $output->setFormatter($formatter);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter()
    {
        foreach($this->outputs as $output) {
            return $output->getFormatter();
        }
    }
}