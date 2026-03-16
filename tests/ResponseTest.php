<?php

namespace AdinanCenci\Psr7\Tests;

use AdinanCenci\Psr7\Response;
use Http\Psr7Test\ResponseIntegrationTest;

class ResponseTest extends ResponseIntegrationTest
{
    public function createSubject()
    {
        return new Response();
    }

    public function testMinimalCookieHeader()
    {
        $response = new Response();
        $response = $response->withAddedCookie('cookie_name', 'cookie value');

        $this->assertEquals('cookie_name=cookie value', $response->getHeaders()['Set-Cookie'][0]);
    }

    public function testCompleteCookieHeader()
    {
        $response = new Response();

        $now = time();
        $twentyFourHours = 60 * 60 * 24;

        $response = $response->withAddedCookie(
            'cookie_name',
            'cookie value',
            $twentyFourHours,
            $now + $twentyFourHours,
            '/',
            'my-domain.com',
            true,
            true,
            'Strict'
        );

        $this->assertEquals(
            'cookie_name=cookie value; MaxAge=86400; Expires=' . date('D, d M Y H:i:s', $now + $twentyFourHours) . ' GMT; Domain=my-domain.com; Path=/; SameSite=Strict; Secure; HttpOnly',
            $response->getHeaders()['Set-Cookie'][0]
        );
    }
}
