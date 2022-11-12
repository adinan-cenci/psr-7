<?php 
namespace AdinanCenci\Psr7\Tests;

use AdinanCenci\Psr7\ServerRequest;
use Http\Psr7Test\ServerRequestIntegrationTest;

class ServerRequestTest extends ServerRequestIntegrationTest
{
    public function createSubject() 
    {
        return new ServerRequest();
    }
}
