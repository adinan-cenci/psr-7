<?php 
namespace AdinanCenci\Psr7\Tests;

use AdinanCenci\Psr7\Request;
use Http\Psr7Test\RequestIntegrationTest;

class RequestTest extends RequestIntegrationTest
{
    public function createSubject() 
    {
        return new Request();
    }
}
