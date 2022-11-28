<?php
namespace AdinanCenci\Psr7;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements MessageInterface, RequestInterface 
{
    protected ?string $target = null;

    protected ?string $method = null;

    protected ?UriInterface $uri = null;

    public function __construct(string $protocolVersion = '1.0', array $headers = [], ?StreamInterface $body = null, string $target = '', string $method = 'GET', ?UriInterface $uri = null) 
    {
        parent::__construct($protocolVersion, $headers, $body);

        $this->validateMethod($method);

        $this->target = $target;
        $this->method = $method;
        $this->uri    = $uri ?? new Uri();
    }

    public function getRequestTarget() 
    {
        if ($this->target) {
            return $this->target;
        }

        if ($this->target === null) {
            return '/';
        }

        if ($this->uri === null) {
            return '/';
        }

        $target = $this->uri->getPath();

        $target = $target 
            ? '/' . ltrim($target, '/') 
            : '/';

        if ($query = $this->uri->getQuery()) {
            $target .= '?' . $query;
        }

        return $target;
    }

    public function withRequestTarget($requestTarget) 
    {
        return $this->instantiate(['target' => $requestTarget]);
    }

    public function getMethod() 
    {
        return $this->method;
    }

    public function withMethod($method) 
    {
        $this->validateMethod($method);
        return $this->instantiate(['method' => $method]);
    }

    public function getUri() 
    {
        return $this->uri;
    }

    /*
     * - If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * - If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * - If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     */

    public function withUri(UriInterface $uri, $preserveHost = false) 
    {
        $headers    = $this->headers;
        $hasHost    = (bool) self::arrayGetKey($headers, 'host');
        $updateHost = (bool) $uri->getHost();

        if ($preserveHost) {
            if (!$hasHost && $uri->getHost()) {
                $updateHost = true;
            }
            else if (!$hasHost && !$uri->getHost()) {
                $updateHost = false;
            }
            else if ($hasHost) {
                $updateHost = false;
            }
        }

        if ($updateHost) {
            $headers = self::arraySetKey($headers, 'host', $uri->getHost());
        }

        return $this->instantiate(['uri' => $uri, 'headers' => $headers]);
    }

    protected function validateMethod($method) 
    {
        if (! is_string($method)) {
            throw new \InvalidArgumentException('The method parameter must be a string');
        }

        $method = strtolower($method);

        if (in_array($method, ['', 'get', 'post', 'put', 'delete', 'options', 'patch', 'head', 'custom'])) {
            return true;
        }

        throw new \InvalidArgumentException('Unrecognized "' . $method . '" method');
    }

    protected function getConstructorParameters() 
    {
        return [
            'protocolVersion' => $this->protocolVersion, 
            'headers'         => $this->headers, 
            'body'            => $this->body,
            'target'          => $this->target,
            'method'          => $this->method,
            'uri'             => $this->uri
        ];
    }
}
