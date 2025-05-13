<?php

namespace AdinanCenci\Psr7;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements MessageInterface, RequestInterface
{
    /**
     * The message's request target.
     */
    protected ?string $target = null;

    /**
     * The HTTP method.
     *
     * @var string
     */
    protected ?string $method = null;

    /**
     * The URI of the message.
     *
     * @var null|Psr\Http\Message\UriInterface
     */
    protected ?UriInterface $uri = null;

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
     */
    public function __construct(
        string $protocolVersion = '1.0',
        array $headers = [],
        ?StreamInterface $body = null,
        string $target = '',
        string $method = 'GET',
        ?UriInterface $uri = null
    ) {
        parent::__construct($protocolVersion, $headers, $body);

        $this->validateMethod($method);

        $this->target = $target;
        $this->method = $method;
        $this->uri    = $uri ?? new Uri();
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function withRequestTarget($requestTarget)
    {
        return $this->instantiate(['target' => $requestTarget]);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function withMethod($method)
    {
        $this->validateMethod($method);
        return $this->instantiate(['method' => $method]);
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $headers    = $this->headers;
        $hasHost    = (bool) self::arrayGetKey($headers, 'host');
        $updateHost = (bool) $uri->getHost();

        if ($preserveHost) {
            if (!$hasHost && $uri->getHost()) {
                $updateHost = true;
            } elseif (!$hasHost && !$uri->getHost()) {
                $updateHost = false;
            } elseif ($hasHost) {
                $updateHost = false;
            }
        }

        if ($updateHost) {
            $headers = self::arraySetKey($headers, 'host', $uri->getHost());
        }

        return $this->instantiate(['uri' => $uri, 'headers' => $headers]);
    }

    /**
     * Validates a HTTP method.
     *
     * @param string $method
     *   String to validate.
     *
     * @throws \InvalidArgumentException
     *   If the method is not valid.
     *
     * @return bool
     *   True if it is valid.
     */
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

    /**
     * {@inheritdoc}
     */
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
