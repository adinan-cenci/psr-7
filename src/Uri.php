<?php
namespace AdinanCenci\Psr7;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface 
{
    protected $scheme = '';

    protected $path = '';

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

    public function getPath() 
    {
        if (empty($this->path)) {
            return '';
        }

        // Straight up stolen from slimphp/Slim-Psr7
        // Do not encode \w, _, -, & etc nor % encoded characters.
        return $this->urlEncode('/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/', $this->path);
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

    protected function urlEncode(string $regex, string $string): string
    {
        return preg_replace_callback($regex, function ($match) 
            {
                return rawurlencode($match[0]);
            },
            $string
        );
    }
}
