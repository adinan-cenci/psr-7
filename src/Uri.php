<?php
namespace AdinanCenci\Psr7;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface 
{
    protected $scheme = '';

    protected $port = null;

    protected static $standardPorts = [
        'http'  => 80,
        'https' => 443,
        'ftp'   => 21,
        'smtp'  => 587,
        'imap'  => 993,
    ];

    public function getScheme() 
    {
        $scheme = (string) $this->scheme;
        $scheme = trim($scheme, ':');
        $scheme = strtolower($scheme);
        return $scheme;
    }

    public function getPort() 
    {
        $port = $this->port;
        $scheme = $this->getScheme();

        if (is_null($port) && empty($scheme)) {
            return null;
        }

        if (is_null($port) && !empty($scheme)) {
            $standard = self::getStandardPort($scheme);
            return $standard == 0 ? null : $standard;
        }

        $port = (int) $port;

        if (self::isStandardPort($port, $this->getScheme())) {
            return null;
        }

        return $port;
    }

    public static function isStandardPort(int $port, string $scheme) : bool
    {
        return isset(self::$standardPorts[$scheme])
            ? self::$standardPorts[$scheme] == $port
            : false;
    }

    public static function getStandardPort(string $scheme) : int
    {
        return isset(self::$standardPorts[$scheme])
            ? self::$standardPorts[$scheme]
            : 0;
    }
}
