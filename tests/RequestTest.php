<?php

namespace AdinanCenci\Psr7\Tests;

use AdinanCenci\Psr7\Request;
use Http\Psr7Test\RequestIntegrationTest;
use Psr\Http\Message\StreamInterface;

class RequestTest extends RequestIntegrationTest
{
    public function createSubject()
    {
        return new Request();
    }

    public function testNewRequestWithEmptyBody()
    {
        $request = new Request();
        $body = $request->getBody();

        $this->assertInstanceOf(StreamInterface::class, $body);
    }
}
