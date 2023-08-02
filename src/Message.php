<?php
namespace AdinanCenci\Psr7;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface 
{
    use FunctionalInstantiationTrait;

    protected string $protocolVersion = '1.0';

    protected array $headers = [];

    protected ?StreamInterface $body = null;

    public function __construct(string $protocolVersion = '1.0', array $headers = [], ?StreamInterface $body = null) 
    {
        $this->validateHeaders($headers);

        $this->protocolVersion = $protocolVersion;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function getProtocolVersion() 
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version) 
    {
        return $this->instantiate(['protocolVersion' => $version]);
    }

    public function getHeaders() 
    {
        return $this->headers;
    }

    public function hasHeader($name) 
    {
        return self::arrayHasKey($this->headers, $name);
    }

    public function getHeader($name) 
    {
        return self::arrayGetKey($this->headers, $name);
    }

    public function getHeaderLine($name) 
    {
        $header = $this->getHeader($name);
        return $header ? implode(', ', $header) : '';
    }

    public function withHeader($name, $value) 
    {
        $this->validateHeaderName($name);
        $this->validateHeaderValue($value);

        $headers = $this->headers;
        $headers = self::arraySetKey($headers, $name, $value);
        return $this->instantiate(['headers' => $headers]);
    }

    public function withAddedHeader($name, $value) 
    {
        $this->validateHeaderName($name);
        $this->validateHeaderValue($value);

        $headers = $this->headers;
        $headers = self::arrayAddKey($headers, $name, $value);
        return $this->instantiate(['headers' => $headers]);
    }

    public function withoutHeader($name) 
    {
        $headers = $this->headers;
        $headers = self::arrayUnsetKey($headers, $name);
        return $this->instantiate(['headers' => $headers]);
    }

    public function getBody() 
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body) 
    {
        return $this->instantiate(['body' => $body]);
    }

    protected function getConstructorParameters() 
    {
        return [
            'protocolVersion' => $this->protocolVersion, 
            'headers' => $this->headers, 
            'body' => $this->body
        ];
    }

    protected function validateHeaders(array $headers) 
    {
        foreach ($headers as $name => $value) {
            $this->validateHeaderName($name);
            $this->validateHeaderValue($value);
        }

        return true;
    }

    protected function validateHeaderName($name) 
    {
        if (!is_string($name) || $name == '') {
            throw new \InvalidArgumentException('Header name parameter must be a string');
        }

        return true;
    }

    protected function validateHeaderValue($value) 
    {
        if (is_string($value)) {
            return true;
        }

        if (is_array($value) && !empty($value) && self::isArrayOfStrings($value)) {
            return true;
        }

        throw new \InvalidArgumentException('Headers must be strings or array of strings');
    }

    public static function arrayGetKey(array $array, string $target) : array
    {
        $target = strtolower($target);

        foreach ($array as $key => $value) {
            if (strtolower($key) != $target) {
                continue;
            }

            return (array) $value;
        }

        return [];
    }

    public static function arrayHasKey(array $array, string $target) : bool
    {
        $ltarget = strtolower($target);

        foreach ($array as $key => $value) {
            if (strtolower($key) == $ltarget) {
                return true;
            }
        }

        return false;
    }

    public static function arrayAddKey(array $array, string $target, $value) : array
    {
        $ltarget = strtolower($target);

        foreach ($array as $key => $v) {
            if (strtolower($key) == $ltarget) {
                $array[$key] = array_merge($array[$key], array_values((array) $value));
                return $array;
            }
        }

        $array[$target] = (array) $value;
        return $array;
    }

    public static function arraySetKey(array $array, string $target, $value) : array
    {
        $ltarget = strtolower($target);

        foreach ($array as $key => $v) {
            if (strtolower($key) == $ltarget) {
                $array[$key] = $value;
                return $array;
            }
        }

        $array[$target] = (array) $value;
        return $array;
    }

    public static function arrayUnsetKey(array $array, string $target) : array
    {
        $ltarget = strtolower($target);

        foreach ($array as $key => $value) {
            if (strtolower($key) == $ltarget) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    public static function isString($value) : bool
    {
        return is_string($value) || is_numeric($value);
    }

    public static function isArrayOfStrings($value) : bool
    {
        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $v) {
            if (self::isString($v)) {
                continue;
            }

            return false;
        }

        return true;
    }
}
