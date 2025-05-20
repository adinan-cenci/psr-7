<?php

namespace AdinanCenci\Psr7\Tests;

use AdinanCenci\Psr7\Uri;
use Http\Psr7Test\UriIntegrationTest;

class UriTest extends UriIntegrationTest
{
    public function createUri($uri)
    {
        return Uri::parseString($uri);
    }
}
