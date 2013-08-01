<?php

/*
* This file is part of the Sonata project.
*
* (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sonata\Composer;

use Sonata\Composer\Reporter\EmailReporter;
use Sonata\Composer\Reporter\Message;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Yaml;
use Sonata\Composer\Reporter\ReporterInterface;

class ComposerApplication extends Application
{
    protected $configuration = array();

    protected $reporters = array();

    /**
     * @param string $configurationFile
     * @param null   $name
     * @param null   $version
     */
    public function __construct($configurationFile = null, $name = null, $version = null)
    {
        parent::__construct($name, $version);

        $this->configure($configurationFile);
    }

    /**
     * @param $configurationFile
     */
    public function configure($configurationFile = null)
    {
        $config = array();
        if (is_file($configurationFile)) {
            $config = Yaml::parse(file_get_contents($configurationFile));
        }

        $configuration = new Configuration();
        $processor = new Processor();

        $this->configuration = $processor->processConfiguration($configuration, array($config));
        $this->configureReporters();
    }

    /**
     * Configure reporters
     */
    public function configureReporters()
    {
        if ($this->configuration['reporting']['mailer']['enabled']) {
            $this->reporters['mailer'] = new EmailReporter($this->configuration['reporting']['mailer']);
        }
    }

    /**
     * @param Message $message
     */
    public function sendReport(Message $message)
    {
        foreach ($this->reporters as $reporter) {
            $reporter->handle($message);
        }
    }

    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getConfiguration($name, $default = null)
    {
        if (!isset($this->configuration[$name])) {
            return $default;
        }

        return $this->configuration[$name];
    }

    /**
     * Returns the git executable
     *
     * return string
     */
    public function getGitExecutable()
    {
        return $this->getConfiguration('git', 'git');
    }

    /**
     * Return the composer executable
     *
     * @return string
     */
    public function getComposerExecutable()
    {
        return $this->getConfiguration('composer', 'composer');
    }

    /**
     * @return ReporterInterface[]
     */
    public function getReporters()
    {
        return $this->reporters;
    }
}
