<?php

namespace AdinanCenci\Psr7;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Response extends Message implements ResponseInterface, MessageInterface
{
    /**
     * The HTTP status code of the response.
     *
     * @var int
     */
    protected int $statusCode;

    /**
     * The response reason phrase associated with the status code.
     *
     * @var string
     */
    protected string $reasonPhrase;

    /**
     * Constructor.
     *
     * @param string $protocolVersion
     *   The version of the HTTP protocol.
     * @param array $headers
     *   HTTP headers.
     * @param null|Psr\Http\Message\StreamInterface $body
     *   The body of the message.
     * @var int $statusCode
     *   The HTTP status code of the response.
     * @var string $reasonPhrase
     *   The response reason phrase associated with the status code.
     */
    public function __construct(
        string $protocolVersion = '1.0',
        array $headers = [],
        ?StreamInterface $body = null,
        int $statusCode = 200,
        string $reasonPhrase = ''
    ) {
        $this->validateStatusCode($statusCode);

        parent::__construct($protocolVersion, $headers, $body);
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $this->validateStatusCode($code);
        return $this->instantiate(['statusCode' => $code, 'reasonPhrase' => $reasonPhrase]);
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    /**
     * Return an instance with the specified cookie appended.
     *
     * Not part of the PSR-7 specification, just something helpful.
     *
     * @param string $name
     *   The name of the cookie.
     * @param string $value
     *   The value of the cookie.
     * @param int|null
     *   Max age of the cookies in seconds.
     * @param \DateTime|int|null
     *   The date the cookie is supposed to expire.
     * @param string
     *   Specific URL path the cookie should be accessible to.
     * @param string
     *   Domain the cookies should be accessible to.
     * @param bool
     *   Whether the cookie should be sent over ssl only.
     * @param bool
     *   Whether the cookies should be accessed through java-script.
     * @param string
     *   Cross-site setting.
     */
    public function withAddedCookie(
        string $name,
        string $value,
        ?int $expires = null,
        string $path = '',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = false
    ) {
        $cookie = new Cookie($name, $value, null, $expires, $path, $domain, $secure, $httpOnly);
        return $this->withAddedHeader('Set-Cookie', (string) $cookie);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConstructorParameters()
    {
        $params = parent::getConstructorParameters();
        $params += [
            'statusCode'    => $this->statusCode,
            'reasonPhrase'  => $this->reasonPhrase
        ];

        return $params;
    }

    /**
     * Validates a HTTP status code.
     *
     * @param int $code
     *   Status code to validate.
     *
     * @throws \InvalidArgumentException
     *   If the status is not valid.
     *
     * @return bool
     *   True if it is valid.
     */
    protected function validateStatusCode($code)
    {
        if (!is_int($code) || $code < 100 || $code > 599) {
            throw new \InvalidArgumentException('Invalid status code');
        }

        return true;
    }
}
