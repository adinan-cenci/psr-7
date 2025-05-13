<?php

namespace AdinanCenci\Psr7;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;

class UploadedFile implements UploadedFileInterface
{
    /**
     * @var null|string
     *
     * Absolute path to the file.
     */
    protected ?string $file = null;

    /**
     * @var Psr\Http\Message\StreamInterface
     *
     * Stream object.
     */
    protected StreamInterface $stream;

    /**
     * @var null|string
     *
     * The client filename.
     */
    protected ?string $name = null;

    /**
     * @var null|string
     *
     * The client filetype.
     */
    protected ?string $type = null;

    /**
     * @var null|string
     *
     * Error associated with the uploaded file
     */
    protected ?string $error = null;

    /**
     * @var null|int
     *
     * The file size in bytes.
     */
    protected ?int $size = null;

    /**
     * @var bool
     *
     * Tracks wether the file has been moved.
     */
    protected bool $moved = false;

    /**
     * @var string
     *
     * Absolute path the file has been moved to.
     */
    protected string $movedTo = '';

    /**
     * Constructor.
     *
     * @param resource|string|Psr\Http\Message\StreamInterface $subject
     *   The file.
     * @param null|string $name
     *   The client filename.
     * @param null|string $type
     *   The client filetype.
     * @param null|string $error
     *   Error associated with the uploaded file
     * @param null|int
     *   The file size in bytes.
     */
    public function __construct(
        $subject,
        ?string $name = null,
        ?string $type = null,
        ?string $error = null,
        ?int $size = null
    ) {
        $this->validateSubject($subject);

        if (is_string($subject)) {
            $this->file = self::getAbsolutePath($subject);
        } elseif ($subject instanceof StreamInterface) {
            $this->stream = $subject;
        } elseif (gettype($subject) == 'resource') {
            $this->stream = new Stream($subject);
        }

        $this->name    = $name;
        $this->type    = $type;
        $this->error   = $error;
        $this->size    = $size;
    }

    /**
     * {@inheritdoc}
     */
    public function getStream()
    {
        if ($this->moved) {
            throw new \RuntimeException('File has previously been moved to ' . $this->movedTo);
        }

        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
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

        isset($this->stream)
            ? $this->moveStream($targetPath)
            : $this->moveFile($targetPath);

        $this->moved = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientMediaType()
    {
        return $this->type;
    }

    /**
     * Moves the file to the specified path.
     *
     * @param string $targetPath
     *   Absolute path.
     *
     * @throws \RuntimeException
     *   If the file does not exist.
     */
    protected function moveFile(string $pargetPath)
    {
        if (! file_exists($this->file)) {
            throw new \RuntimeException('File ' . $this->file . ' does not exist');
        }

        self::isInsideTempDir($this->file)
            ? move_uploaded_file($this->file, $pargetPath)
            : rename($this->file, $pargetPath);
    }

    /**
     * Moves the stream to the specified path.
     *
     * @param string $targetPath
     *   Absolute path.
     *
     * @throws \RuntimeException
     *   If the stream is not readable.
     */
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

    /**
     * Validates if $subject is a valid file.
     *
     * A resource, a stream or a file.
     *
     * @param resource|string|Psr\Http\Message\StreamInterface $subject
     *   The file.
     *
     * @throws InvalidArgumentException
     *   If it is not valid.
     *
     * @return bool
     *   True if it is valid.
     */
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

    /**
     * Checks if $path exists in the filesystem.
     *
     * @param string $path
     *   File path.
     *
     * @return bool
     *   True if it exists.
     */
    protected static function isInsideTheFileSystem($path)
    {
        return file_exists($path);
    }

    /**
     * Checks if $path is inside the system's temp dir.
     *
     * @param string $path
     *   File path.
     *
     * @return bool
     *   True if it is inside the temp dir.
     */
    protected static function isInsideTempDir(string $path): bool
    {
        return substr_count($path, sys_get_temp_dir()) > 0;
    }

    /**
     * Makes sure a path is absolute.
     *
     * If it is already absolute, nothing changes.
     *
     * @param string $targetPath
     *   File path.
     *
     * @return string
     *   An absolute path.
     */
    protected static function getAbsolutePath(string $targetPath): string
    {
        return self::isAbsolutePath($targetPath)
            ? $targetPath
            : self::getCwd() . $targetPath;
    }

    /**
     * Returns the current working directory.
     *
     * @return string
     *   Current directory.
     */
    protected static function getCwd(): string
    {
        $cwd = getcwd();
        $cwd = str_replace('\\', '/', $cwd);
        return rtrim($cwd, '/') . '/';
    }

    /**
     * Checks if a path is absolute.
     *
     * @param string $path
     *   File path.
     *
     * @return bool
     *   True if $path is absolute.
     */
    protected static function isAbsolutePath(string $path): bool
    {
        return preg_match('/^([A-Za-z]\:)?\//', $path);
    }
}
