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
}
