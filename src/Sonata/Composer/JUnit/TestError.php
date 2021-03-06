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

class TestError extends TestMessage
{
    /**
     * @return string
     */
    public function toXml()
    {
        return sprintf('<error type="%s">%s</error>',
            Utils::encodeXml($this->getType()),
            Utils::cdata($this->getMessage())
        );
    }
}