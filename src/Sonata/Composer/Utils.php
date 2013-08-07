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


final class Utils
{
    private function __construct()
    {}

    /**
     * @param string $string
     *
     * @return string
     */
    public function encodeXml($string)
    {
        return htmlspecialchars($string,  ENT_COMPAT | ENT_HTML401, 'UTF-8');
    }

    public function cdata($string)
    {
        return self::encodeXml($string);
    }
}

