<?php

namespace AdinanCenci\Psr7;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface, MessageInterface, RequestInterface
{
    /**
     * Server parameters.
     *
     * @var array
     */
    protected array $serverParams;

    /**
     * Cookie parameters.
     *
     * @var array
     */
    protected array $cookieParams;

    /**
     * Query parameters.
     *
     * @var array
     */
    protected array $queryParams;

    /**
     * Derived request attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * The parsed body of the request.
     *
     * @var mixed
     */
    protected $parsedBody;

    /**
     * Uploaded files.
     *
     * @var Psr\Http\Message\UploadedFileInterface[] $uploadedFiles
     */
    protected array $uploadedFiles;

    /**
     * Constructor.
     *
     * @param string $protocolVersion
     *   The version of the HTTP protocol.
     * @param array $headers
     *   HTTP headers.
     * @param null|Psr\Http\Message\StreamInterface $body
     *   The body of the message.
     * @param string $target
     *   The message's request target.
     * @param string $method
     *   The HTTP method.
     * @param null|Psr\Http\Message\UriInterface $uri
     *   The URI of the message.
     * @param array $cookieParams
     *   Cookie parameters.
     * @param array $queryParams
     *   Query parameters.
     * @param array $attributes
     *   Derived request attributes.
     * @param mixed $parsedBody
     *   The parsed body of the request.
     * @param Psr\Http\Message\UploadedFileInterface[] $uploadedFiles
     *   Uploaded files.
     * @param array $serverParams
     *   Server parameters.
     */
    public function __construct(
        string $protocolVersion = '1.0',
        array $headers = [],
        ?StreamInterface $body = null,
        string $target = '',
        string $method = 'GET',
        ?UriInterface $uri = null,
        array $cookieParams = [],
        array $queryParams = [],
        array $attributes = [],
        $parsedBody = null,
        array $uploadedFiles = [],
        array $serverParams = []
    ) {
        $this->validateParsedBody($parsedBody);

        parent::__construct($protocolVersion, $headers, $body, $target, $method, $uri);
        $this->cookieParams  = $cookieParams;
        $this->queryParams   = $queryParams;
        $this->attributes    = $attributes;
        $this->parsedBody    = $parsedBody;
        $this->uploadedFiles = $uploadedFiles;
        $this->serverParams  = $serverParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies)
    {
        return $this->instantiate(['cookieParams' => $cookies]);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query)
    {
        return $this->instantiate(['queryParams' => $query]);
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        return $this->instantiate(['uploadedFiles' => $uploadedFiles]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data)
    {
        $this->validateParsedBody($data);
        return $this->instantiate(['parsedBody' => $data]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        return isset($this->attributes[$name])
            ? $this->attributes[$name]
            : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute($name, $value)
    {
        $attributes = $this->attributes;
        $attributes[$name] = $value;
        return $this->instantiate(['attributes' => $attributes]);
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute($name)
    {
        $attributes = $this->attributes;
        unset($attributes[$name]);
        return $this->instantiate(['attributes' => $attributes]);
    }

    /**
     * Retrieves post data from the request.
     *
     * Not part of the PSR-7.
     *
     * @param string $name
     *   The name of the variable.
     * @param mixed $default
     *   The default value if $name is undefined.
     *
     * @return mixed
     *   The post variable if available, $default if it is not.
     */
    public function post(string $name, $default = null)
    {
        $contentType = $this->getHeaderLine('content-type');

        if (! $contentType) {
            return $default;
        }

        $mime = $this->getMime((string) $contentType);

        if (! in_array($mime, ['application/x-www-form-urlencoded', 'multipart/form-data', ''])) {
            return $default;
        }

        return is_array($this->parsedBody) && isset($this->parsedBody[$name])
            ? $this->parsedBody[$name]
            : $default;
    }

    /**
     * Retrieves query parameters from the request.
     *
     * Not part of the PSR-7.
     *
     * @param string $name
     *   The name of the variable.
     * @param mixed $default
     *   The default value if $name is undefined.
     *
     * @return mixed
     *   The get variable if available, $default if it is not.
     */
    public function get(string $name, $default = null)
    {
        return isset($this->queryParams[$name])
            ? $this->queryParams[$name]
            : $default;
    }

    /**
     * Retrieves cookie data from the request.
     *
     * Not part of the PSR-7.
     *
     * @param string $name
     *   The name of the variable.
     * @param mixed $default
     *   The default value if $name is undefined.
     *
     * @return mixed
     *   The cookie variable if available, $default if it is not.
     */
    public function cookie(string $name, $default = null)
    {
        return isset($this->cookieParams[$name])
            ? $this->cookieParams[$name]
            : $default;
    }

    /**
     * Retrieves server data from the request.
     *
     * @param string $name
     *   The name of the variable.
     * @param mixed $default
     *   The default value if $name is undefined.
     *
     * @return mixed
     *   The server variable if available, $default if it is not.
     */
    public function server(string $name, $default = null)
    {
        return isset($this->serverParams[$name])
            ? $this->serverParams[$name]
            : $default;
    }

    /**
     * Extracts the mime type from a content-type http header.
     *
     * @param string $contentType
     *   The content-type header.
     *
     * @return string
     *   The mime type.
     */
    protected static function getMime(string $contentType): string
    {
        return preg_match('#^([^;]+)#', $contentType, $matches)
            ? trim(strtolower($matches[1]))
            : $contentType;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConstructorParameters()
    {
        $params = parent::getConstructorParameters();
        $params += [
            'cookieParams'  => $this->cookieParams,
            'queryParams'   => $this->queryParams,
            'attributes'    => $this->attributes,
            'parsedBody'    => $this->parsedBody,
            'uploadedFiles' => $this->uploadedFiles,
            'serverParams'  => $this->serverParams
        ];

        return $params;
    }

    /**
     * Validates the message's parsed body.
     *
     * @param mixed $body
     *   The body to validate.
     *
     * @throws \InvalidArgumentException
     *   If the body is not valid.
     *
     * @return bool
     *   True if it is valid.
     */
    protected function validateParsedBody($body)
    {
        if (!is_array($body) && !is_null($body) && !is_object($body)) {
            throw new \InvalidArgumentException('Body should be an array, object or null');
        }

        return true;
    }
}
