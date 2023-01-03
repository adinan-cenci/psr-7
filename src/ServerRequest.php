<?php
namespace AdinanCenci\Psr7;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequest extends Request implements ServerRequestInterface, MessageInterface, RequestInterface 
{
    protected array $serverParams;

    protected array $cookieParams;

    protected array $queryParams;

    protected array $attributes;

    protected $parsedBody;

    protected array $uploadedFiles;

    public function __construct(string $protocolVersion = '1.0', array $headers = [], ?StreamInterface $body = null, string $target = '', string $method = 'GET', ?UriInterface $uri = null, array $cookieParams = [], array $queryParams = [], array $attributes = [], $parsedBody = null, array $uploadedFiles = [], array $serverParams = []) 
    {
        $this->validateParsedBody($parsedBody);

        parent::__construct($protocolVersion, $headers, $body, $target, $method, $uri);
        $this->cookieParams  = $cookieParams;
        $this->queryParams   = $queryParams;
        $this->attributes    = $attributes;
        $this->parsedBody    = $parsedBody;
        $this->uploadedFiles = $uploadedFiles;
        $this->serverParams  = $serverParams;
    }

    public function getServerParams() 
    {
        return $this->serverParams;
    }

    public function getCookieParams() 
    {
        return $this->cookieParams;
    }

    public function withCookieParams(array $cookies) 
    {
        return $this->instantiate(['cookieParams' => $cookies]);
    }

    public function getQueryParams() 
    {
        return $this->queryParams;
    }

    public function withQueryParams(array $query) 
    {
        return $this->instantiate(['queryParams' => $query]);
    }

    public function getUploadedFiles() 
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles) 
    {
        return $this->instantiate(['uploadedFiles' => $uploadedFiles]);
    }

    public function getParsedBody() 
    {
        return $this->parsedBody;
    }

    public function withParsedBody($data) 
    {
        $this->validateParsedBody($data);
        return $this->instantiate(['parsedBody' => $data]);
    }

    public function getAttributes() 
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null) 
    {
        return isset($this->attributes[$name])
            ? $this->attributes[$name]
            : $default;
    }

    public function withAttribute($name, $value) 
    {
        $attributes = $this->attributes;
        $attributes[$name] = $value;
        return $this->instantiate(['attributes' => $attributes]);
    }

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
     * @param string $name;
     * @param mixed $default null
     *   If $name is undefined then $default will be returned.
     * 
     * @return mixed
     */
    public function post(string $name, $default = null) 
    {
        $contentType = $this->getHeaderLine('content-type');

        if (! $contentType) {
            return $default;
        }

        if (! in_array($contentType, ['application/x-www-form-urlencoded', 'multipart/form-data', null])) {
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
     * @param string $name;
     * @param mixed $default null
     *   If $name is undefined then $default will be returned.
     * 
     * @return mixed
     */
    public function get(string $name, $default = null) 
    {
        return isset($this->queryParams[$name])
            ? $this->queryParams[$name]
            : null;
    }

    /**
     * Retrieves cookie data from the request.
     * 
     * Not part of the PSR-7.
     * 
     * @param string $name;
     * @param mixed $default null
     *   If $name is undefined then $default will be returned.
     * 
     * @return mixed
     */
    public function cookie(string $name, $default = null) 
    {
        return isset($this->queryParams[$name])
            ? $this->queryParams[$name]
            : null;
    }

    /**
     * Retrieves server data from the request.
     * 
     * Not part of the PSR-7.
     * 
     * @param string $name;
     * @param mixed $default null
     *   If $name is undefined then $default will be returned.
     * 
     * @return mixed
     */
    public function server(string $name, $default = null) 
    {
        return isset($this->serverParams[$name])
            ? $this->serverParams[$name]
            : null;
    }

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

    protected function validateParsedBody($body) 
    {
        if (!is_array($body) && !is_null($body) && !is_object($body)) {
            throw new \InvalidArgumentException('Body should be an array, object or null');
        }

        return true;
    }
}
