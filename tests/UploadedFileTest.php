<?php

namespace AdinanCenci\Psr7\Tests;

use AdinanCenci\Psr7\Stream;
use AdinanCenci\Psr7\UploadedFile;
use Http\Psr7Test\UploadedFileIntegrationTest;

class UploadedFileTest extends UploadedFileIntegrationTest
{
    public function createSubject()
    {
        return new UploadedFile(new Stream(fopen('php://memory', 'rw')));
    }
}
