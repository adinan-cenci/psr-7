<?php 
namespace AdinanCenci\Psr7\Tests;

use AdinanCenci\Psr7\Stream;
use Http\Psr7Test\StreamIntegrationTest;

class StreamTest extends StreamIntegrationTest
{
    public function createStream($data) 
    {
        return new Stream($data);
    }
}
