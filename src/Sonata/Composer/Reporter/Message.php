<?php

/*
* This file is part of the Sonata project.
*
* (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sonata\Composer\Reporter;

class Message
{
    protected $content;

    protected $status;

    protected $project;

    /**
     * @param string $project
     * @param string $content
     * @param string $status
     */
    public function __construct($project, $content, $status)
    {
        $this->project = $project;
        $this->content = $content;
        $this->status = $status;
    }

    /**
     * @param string $project
     * @param string $content
     * @param string $status
     */
    static function create($project, $content, $status)
    {
        return new self($project, $content, $status);
    }

    /**
     * @return string
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}