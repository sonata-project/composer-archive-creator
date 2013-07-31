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

use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Yaml;

class ComposerApplication extends Application
{
    protected $configuration = array();

    public function __construct($configurationFile, $name = null, $version = null)
    {
        parent::__construct($name, $version);

        if (is_file($configurationFile)) {
            $this->configuration = Yaml::parse($configurationFile);
        }
    }

    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getConfiguration($name, $default)
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
}