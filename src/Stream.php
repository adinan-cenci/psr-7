<?php

namespace AdinanCenci\Psr7;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * File pointer resource.
     *
     * @var resource
     */
    protected $resource = null;

    /**
     * Constructor.
     *
     * @param resource $resource
     *   File pointer resource.
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $this->seek(0);
        return $this->getContents();
    }

    /**
     * Serializes the stream.
     *
     * Not part of the PSR-7.
     */
    public function __serialize(): array
    {
        return [
            'content' => $this->__toString()
        ];
    }

    /**
     * Unserializes the stream.
     *
     * Not part of the PSR-7.
     */
    public function __unserialize(array $data): void
    {
        $r = fopen('php://memory', 'r+');
        fwrite($r, $data['content']);
        $this->resource = $r;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        fclose($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        $info = fstat($this->resource);
        if (isset($info['size'])) {
            return $info['size'];
        }

        $size = 0;
        $chunkSize = 8192;

        while ($content = fread($this->resource, $chunkSize)) {
            $size += strlen($content);
        }

        rewind($this->resource);

        return $size;
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        $position = ftell($this->resource);

        if ($position === false) {
            throw new \RuntimeException('Could not retrieve the pointer\'s position');
        }

        return $position;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return feof($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return $this->getMetadata('seekable');
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (! $this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }

        fseek($this->resource, $offset, $whence);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        $mode = $this->getMetadata('mode');

        return substr_count($mode, 'r+') ||
        substr_count($mode, 'w') ||
        substr_count($mode, 'a') ||
        substr_count($mode, 'x') ||
        substr_count($mode, 'c+');
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        if (! $this->isWritable()) {
            throw new \RuntimeException('Stream is not writable');
        }

        return fwrite($this->resource, $string);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        $mode = $this->getMetadata('mode');

        return substr_count($mode, 'r') ||
        substr_count($mode, 'w+') ||
        substr_count($mode, 'a+') ||
        substr_count($mode, 'x+') ||
        substr_count($mode, 'c+');
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        if (! $this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }

        $contents  = '';

        if ($length <= 0) {
            return $contents;
        }

        $chunkSize = 8192;
        $bytesRead = 0;

        while ($bytesRead < $length) {
            $nextStretch = ($bytesRead + $chunkSize) < $length
                ? $chunkSize
                : $length - $bytesRead;

            $chunk = fread($this->resource, $nextStretch);
            $contents .= $chunk;
            $bytesRead += strlen($chunk);

            if (empty($chunk)) {
                break;
            }
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        $length = $this->getSize();
        return $this->read($length);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        $metadata = stream_get_meta_data($this->resource);
        return $key
            ? $metadata[$key]
            : $metadata;
    }
}
