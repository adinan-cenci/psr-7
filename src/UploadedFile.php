<?php
namespace AdinanCenci\Psr7;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;

class UploadedFile implements UploadedFileInterface 
{
    protected ?string $file = null;

    protected StreamInterface $stream;

    protected ?string $name = null;

    protected ?string $type = null;

    protected ?string $error = null;

    protected ?int $size = null;

    protected bool $moved = false;

    protected string $movedTo = '';

    public function __construct($subject, ?string $name = null, ?string $type = null, ?string $error = null, ?int $size = null) 
    {
        $this->validateSubject($subject);

        if (is_string($subject)) {
            $this->file = self::getAbsolutePath($subject);
        } else if ($subject instanceof StreamInterface) {
            $this->stream = $subject;
        } else if (gettype($subject) == 'resource') {
            $this->stream = new Stream($subject);
        }

        $this->name    = $name;
        $this->type    = $type;
        $this->error   = $error;
        $this->size    = $size;
    }

    public function getStream() 
    {
        if ($this->moved) {
            throw new \RuntimeException('File has previously been moved to ' . $this->movedTo);
        }

        return $this->stream;
    }

    public function moveTo($targetPath) 
    {
        if ($this->moved) {
            throw new \RuntimeException('File has previously been moved to ' . $this->movedTo);
        }

        $targetPath = self::getAbsolutePath($targetPath);
        $directory  = dirname($targetPath);

        if (file_exists($targetPath)) {
            throw new \RuntimeException('Target path ' . $targetPath . ' already exists');
        }

        if (! is_writable($directory)) {
            throw new \RuntimeException('Directory ' . $directory . ' is not writable');
        }

        $this->stream
            ? $this->moveStream($targetPath)
            : $this->moveFile($targetPath);

        $this->moved = true;
    }

    public function getSize() 
    {
        return $this->size;
    }

    public function getError() 
    {
        return $this->error;
    }

    public function getClientFilename() 
    {
        return $this->name;
    }

    public function getClientMediaType() 
    {
        return $this->type;
    }

    protected function moveFile(string $pargetPath) 
    {
        if (! file_exists($this->file)) {
            throw new \RuntimeException('File ' . $this->file . ' does not exist');
        }

        self::isInsideTempDir($this->file)
            ? move_uploaded_file($this->file, $pargetPath)
            : rename($this->file, $pargetPath);
    }

    protected function moveStream(string $targetPath) 
    {
        if (! $this->stream->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }

        $dest = new Stream(fopen($targetPath, 'w'));

        $this->stream->rewind();
        while (! $this->stream->eof()) {
            if (! $dest->write($this->stream->read(1048576))) {
                break;
            }
        }

        $original = $this->stream->getMetadata('uri');

        $this->stream->close();

        if (self::isInsideTheFileSystem($original)) {
            unlink($original);
        }
    }

    protected function validateSubject($subject) 
    {
        if ($subject instanceof StreamInterface || gettype($subject) == 'resource') {
            return true;
        }

        if (is_string($subject) && is_file($subject)) {
            return true;
        }

        throw new \InvalidArgumentException('The subject must be a file, resource or an instance of StreamInterface');
    }

    protected static function isInsideTheFileSystem($path) 
    {
        return file_exists($path);
    }

    protected static function isInsideTempDir(string $path) : bool
    {
        return substr_count($path, sys_get_temp_dir()) > 0;
    }

    protected static function getAbsolutePath(string $targetPath) : string
    {
        return self::isAbsolutePath($targetPath)
            ? $targetPath
            : self::getCwd() . $targetPath;
    }

    protected static function getCwd() : string
    {
        $cwd = getcwd();
        $cwd = str_replace('\\', '/', $cwd);
        return rtrim($cwd, '/') . '/';
    }

    protected static function isAbsolutePath(string $path) : bool
    {
        return preg_match('/^([A-Za-z]\:)?\//', $path);
    }
}
