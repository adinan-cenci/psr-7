<?php
namespace AdinanCenci\Psr7;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Response extends Message implements ResponseInterface, MessageInterface 
{
    protected int $statusCode;

    protected string $reasonPhrase;

    public function __construct(string $protocolVersion = '1.0', array $headers = [], ?StreamInterface $body = null, int $statusCode = 200, string $reasonPhrase = '') 
    {
        $this->validateStatusCode($statusCode);

        parent::__construct($protocolVersion, $headers, $body);
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
    }

    public function getStatusCode() 
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = '') 
    {
        $this->validateStatusCode($code);
        return $this->instantiate(['statusCode' => $code, 'reasonPhrase' => $reasonPhrase]);
    }

    public function getReasonPhrase() 
    {
        return $this->reasonPhrase;
    }

    // Not part of the psr7 specification
    public function withAddedCookie(string $name, string $value, ?int $expires = null, string $path = '', string $domain = '', bool $secure = false, bool $httpOnly = false) 
    {
        $cookie = new Cookie($name, $value, null, $expires, $path, $domain, $secure, $httpOnly);
        return $this->withAddedHeader('Set-Cookie', (string) $cookie);
    }

    protected function getConstructorParameters() 
    {
        $params = parent::getConstructorParameters();
        $params += [
            'statusCode'    => $this->statusCode,
            'reasonPhrase'  => $this->reasonPhrase
        ];

        return $params;
    }

    protected function validateStatusCode($code) 
    {
        if (!is_int($code) || $code < 100 || $code > 599) {
            throw new \InvalidArgumentException('Invalid status code');
        }
        
        return true;
    }
}
