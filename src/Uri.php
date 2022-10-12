<?php
namespace AdinanCenci\Psr7;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface 
{
    protected $scheme = '';

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

    public static function isStandardPort(int $port, string $scheme) : bool
    {
        return isset(self::$standardPorts[$scheme])
            ? self::$standardPorts[$scheme] == $port
            : false;
    }
}
