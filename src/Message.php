<?php

namespace AdinanCenci\Psr7;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{
    use FunctionalInstantiationTrait;

    /**
     * The version of the HTTP protocol.
     *
     * @var string
     */
    protected string $protocolVersion = '1.0';

    /**
     * HTTP headers.
     *
     * @var array
     */
    protected array $headers = [];

    /**
     * The body of the message.
     *
     * @var null|Psr\Http\Message\StreamInterface
     */
    protected ?StreamInterface $body = null;

    /**
     * Constructor.
     *
     * @param string $protocolVersion
     *   The version of the HTTP protocol.
     * @param array $headers
     *   HTTP headers.
     * @param null|Psr\Http\Message\StreamInterface $body
     *   The body of the message.
     */
    public function __construct(
        string $protocolVersion = '1.0',
        array $headers = [],
        ?StreamInterface $body = null
    ) {
        $this->validateHeaders($headers);

        $this->protocolVersion = $protocolVersion;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version): MessageInterface
    {
        return $this->instantiate(['protocolVersion' => $version]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name): bool
    {
        return self::arrayHasKey($this->headers, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name): array
    {
        return self::arrayGetKey($this->headers, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name): string
    {
        $header = $this->getHeader($name);
        return $header
            ? implode(', ', $header)
            : '';
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value): MessageInterface
    {
        $this->validateHeaderName($name);
        $this->validateHeaderValue($value);

        $headers = $this->headers;
        $headers = self::arraySetKey($headers, $name, $value);
        return $this->instantiate(['headers' => $headers]);
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value): MessageInterface
    {
        $this->validateHeaderName($name);
        $this->validateHeaderValue($value);

        $headers = $this->headers;
        $headers = self::arrayAddKey($headers, $name, $value);
        return $this->instantiate(['headers' => $headers]);
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name): MessageInterface
    {
        $headers = $this->headers;
        $headers = self::arrayUnsetKey($headers, $name);
        return $this->instantiate(['headers' => $headers]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        return $this->instantiate(['body' => $body]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConstructorParameters()
    {
        return [
            'protocolVersion' => $this->protocolVersion,
            'headers' => $this->headers,
            'body' => $this->body
        ];
    }

    /**
     * Validates headers.
     *
     * @throws \InvalidArgumentException
     *   If a single header is invalid.
     *
     * @return bool
     *   True if all of them are valid.
     */
    protected function validateHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->validateHeaderName($name);
            $this->validateHeaderValue($value);
        }

        return true;
    }

    /**
     * Validates a header name.
     *
     * @param string $name
     *   The header name.
     *
     * @throws \InvalidArgumentException
     *   If the header is not valid.
     *
     * @return bool
     *   True if it is valid.
     */
    protected function validateHeaderName($name)
    {
        if (!is_string($name) || $name == '') {
            throw new \InvalidArgumentException('Header name parameter must be a string');
        }

        return true;
    }

    /**
     * Validates a header value.
     *
     * @param string|array $value
     *   Header value, a string or array of strings.
     *
     * @throws \InvalidArgumentException
     *   If the header is not valid.
     *
     * @return bool
     *   True if it is valid.
     */
    protected function validateHeaderValue($value)
    {
        if (self::isStringOrNumeric($value)) {
            return true;
        }

        if (self::isArrayOfStringsAndOrNumbers($value)) {
            return true;
        }

        throw new \InvalidArgumentException('Headers must be strings or array of strings');
    }

    /**
     * Returns the value for the specified key, case insensitive.
     *
     * @param array $array
     *   The array to retrieve the value from.
     *
     * @param string $target
     *   The key we want to retrieve.
     *
     * @return array
     *   The value alocate in the $target array key. If there is nothing
     *   there an empty array is returned.
     */
    public static function arrayGetKey(array $array, string $target): array
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

    /**
     * Check if the array has the specified key, case insensitive.
     *
     * @param array $array
     *   The array.
     *
     * @param string $target
     *   The key we want to check.
     *
     * @return bool
     *   True if the key is there.
     */
    public static function arrayHasKey(array $array, string $target): bool
    {
        $ltarget = strtolower($target);

        foreach ($array as $key => $value) {
            if (strtolower($key) == $ltarget) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a value to an array in the specified key.
     *
     * If there is a string in $target, it will be transformed into an array
     * and the $value will be appended.
     *
     * If $value is an array, then it will be merged with the value at $target.
     *
     * @param array $array
     *   The array.
     * @param string $target
     *   The key we want to add the value to.
     * @param mixed $value
     *   The value to be added.
     *
     * @return array
     *   The new array with the added value.
     */
    public static function arrayAddKey(array $array, string $target, $value): array
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

    /**
     * Sets a value to an array in the specified key, case insensitive.
     *
     * @param array $array
     *   The array.
     * @param string $target
     *   The key we want to set the value to.
     * @param mixed $value
     *   The value to be set.
     *
     * @return array
     *   The new array with the new value.
     */
    public static function arraySetKey(array $array, string $target, $value): array
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

    /**
     * Removes a key from an array, case insensitive.
     *
     * @param array $array
     *   The array.
     * @param string $target
     *   The key we want to remove from the $array.
     *
     * @return array
     *   The new array with the key removed.
     */
    public static function arrayUnsetKey(array $array, string $target): array
    {
        $ltarget = strtolower($target);

        foreach ($array as $key => $value) {
            if (strtolower($key) == $ltarget) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Checks if a value is a string or a number.
     *
     * @todo Rename the method.
     *
     * @param mixed $value
     *   The value to check.
     *
     * @return bool
     *   Returns true if it is a string or a number.
     */
    public static function isStringOrNumeric($value): bool
    {
        return is_string($value) || is_numeric($value);
    }

    /**
     * Checks if a value is an array of strings and/or numbers.
     *
     * @todo Rename the method.
     *
     * @param mixed $value
     *   The value to check.
     *
     * @return bool
     *   Returns true if the array is comprised of strings and numbers only.
     */
    public static function isArrayOfStringsAndOrNumbers($value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if (!$value) {
            return false;
        }

        foreach ($value as $v) {
            if (self::isStringOrNumeric($v)) {
                continue;
            }

            return false;
        }

        return true;
    }
}
