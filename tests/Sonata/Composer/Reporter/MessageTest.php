<?php

namespace Sonata\Composer\Reporter;

use Sonata\Composer\Reporter\Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testStaticMethod()
    {
        $message = Message::create('project', 'content', 'status');

        $this->assertEquals('project', $message->getProject());
        $this->assertEquals('content', $message->getContent());
        $this->assertEquals('status', $message->getStatus());
    }
}
