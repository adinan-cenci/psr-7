<?php
namespace AdinanCenci\Psr7;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface 
{
    protected $resource = null;

    public function __construct($resource) 
    {
        $this->resource = $resource;
    }

    public function __toString() 
    {
        $this->seek(0);
        return $this->getContents();
    }

    public function __serialize() : array
    {
        return [
            'content' => $this->__toString()
        ];
    }

    public function __unserialize(array $data): void
    {
        $r = fopen('php://memory', 'r+');
        fwrite($r, $data['content']);
        $this->resource = $r;
    }

    public function close() 
    {
        fclose($this->resource);
    }

    public function detach() 
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    public function getSize() 
    {
        $info = fstat($this->resource);
        return $info ? $info['size'] : null;
    }

    public function tell() 
    {
        $position = ftell($this->resource);

        if ($position === false) {
            throw new \RuntimeException('Could not retrieve the pointer\'s position');
        }

        return $position;
    }

    public function eof() 
    {
        return feof($this->resource);
    }

    public function isSeekable() 
    {
        return $this->getMetadata('seekable');
    }

    public function seek($offset, $whence = SEEK_SET) 
    {
        if (! $this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }

        fseek($this->resource, $offset, $whence);
    }

    public function rewind() 
    {
        $this->seek(0);
    }

    public function isWritable() 
    {
        $mode = $this->getMetadata('mode');

        return substr_count($mode, 'r+') || 
        substr_count($mode, 'w') || 
        substr_count($mode, 'a') || 
        substr_count($mode, 'x') || 
        substr_count($mode, 'c+');
    }

    public function write($string) 
    {
        if (! $this->isWritable()) {
            throw new \RuntimeException('Stream is not writable');
        }

        return fwrite($this->resource, $string);
    }

    public function isReadable() 
    {
        $mode = $this->getMetadata('mode');

        return substr_count($mode, 'r') || 
        substr_count($mode, 'w+') || 
        substr_count($mode, 'a+') || 
        substr_count($mode, 'x+') || 
        substr_count($mode, 'c+');
    }

    public function read($length) 
    {
        if (! $this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }

        return $length > 0
            ? fread($this->resource, $length)
            : '';
    }

    public function getContents() 
    {
        $length = $this->getSize();
        return $this->read($length);
    }

    public function getMetadata($key = null) 
    {
        $metadata = stream_get_meta_data($this->resource);
        return $key ? $metadata[$key] : $metadata;
    }
}
