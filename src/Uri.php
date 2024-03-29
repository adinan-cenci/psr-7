<?php
namespace AdinanCenci\Psr7;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface 
{
    use FunctionalInstantiationTrait;

    protected $scheme = '';

    protected $username = '';

    protected $password = '';

    protected $host = '';

    protected $port = null;

    protected $path = '';

    protected $query = '';

    protected $fragment = '';

    protected static $standardPorts = [
        'http'  => 80,
        'https' => 443,
        'ftp'   => 21,
        'smtp'  => 587,
        'imap'  => 993,
    ];

    public function __construct($scheme = '', $username = '', $password = '', $host = '', $port = null, $path = '', $query = '', $fragment = '') 
    {
        $this->validateScheme($scheme);
        $this->validateHost($host);
        $this->validatePort($port);
        $this->validatePath($path);
        $this->validateQuery($query);
        $this->validateFragment($fragment);

        $this->scheme   = $scheme;
        $this->username = $username;
        $this->password = $password;
        $this->host     = $host;
        $this->port     = $port;
        $this->path     = $path;
        $this->query    = $query;
        $this->fragment = $fragment;
    }

    public function __toString() 
    {
        $scheme     = $this->getScheme();
        $authority  = $this->getAuthority();
        $path       = $this->getPath();
        $query      = $this->getQuery();
        $fragment   = $this->getFragment();

        if ($scheme) {
            $scheme = $scheme . ':';
        }

        if ($authority) {
            $authority = '//' . $authority;
        }

        if ($path && $authority && substr($path, 0, 1) != '/') {
            $path = '/' . $path;
        } else if ($path && !$authority) {
            $path = preg_replace('#^/{2,}#', '/', $path);
        }

        if ($query != '') {
            $query = '?' . $query;
        }

        if ($fragment != '') {
            $fragment = '#' . $fragment;
        }

        return $scheme . $authority . $path . $query . $fragment;
    }

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

        if (is_null($port)) {
            return null;
        }

        $port = (int) $port;

        if (self::isStandardPort($port, $this->getScheme())) {
            return null;
        }

        return $port;
    }

    public function getUserInfo() 
    {
        $username = $this->username;
        $password = $this->password;

        if ($username == '') {
            return '';
        }

        $userInfo = $username;

        if ($password != '') {
            $userInfo = $userInfo . ':' . $password;
        }

        return $userInfo;
    }

    public function getHost() 
    {
        $host = $this->host;

        if ($host == '') {
            return '';
        }

        return strtolower($host);
    }

    public function getAuthority() 
    {
        $userInfo = $this->getUserInfo();
        $host = $this->getHost();
        $port = $this->getPort();

        $authority = $host;

        if ($userInfo) {
            $authority = $userInfo . '@' . $authority;
        }

        if ($port) {
            $authority = $authority . ':' . $port;
        }

        return $authority;
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

    public function getQuery() 
    {
        $query = $this->query;

        if ($query == '') {
            return '';
        }

        $query = ltrim($query, '?');

        // Also shamelesly stolen from slimphp/Slim-Psr7
        // Do not encode \w, _, - & etc nor % encoded characters.
        return $this->urlEncode('/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/', $query);
    }

    public function getFragment() 
    {
        $fragment = $this->fragment;

        if ($fragment == '') {
            return '';
        }

        // You know the drill by now
        // Do not encode \w, _, - & etc nor % encoded characters.
        return $this->urlEncode('/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/', $fragment);
    }

    public function withScheme($scheme) 
    {
        $this->validateScheme($scheme);
        return $this->instantiate(['scheme' => $scheme]);
    }

    public function withUserInfo($username, $password = null) 
    {
        $password = (string) $password;
        return $this->instantiate(['username' => $username, 'password' => $password]);
    }

    public function withHost($host) 
    {
        $this->validateHost($host);
        return $this->instantiate(['host' => $host]);
    }

    public function withPort($port) 
    {
        $this->validatePort($port);
        return $this->instantiate(['port' => $port]);
    }

    public function withPath($path) 
    {
        $this->validatePath($path);
        return $this->instantiate(['path' => $path]);
    }

    public function withQuery($query) 
    {
        $this->validateQuery($query);
        return $this->instantiate(['query' => $query]);
    }

    public function withFragment($fragment) 
    {
        $this->validateFragment($fragment);
        return $this->instantiate(['fragment' => $fragment]);
    }

    public static function isValidHost($host) : bool
    {
        return self::isValidHostName($host) || self::isValidIp($host);
    }

    public static function isValidHostName($hostname) : bool
    {
        return (bool) filter_var($hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
    }

    public static function isValidIp($ip) : bool
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP);
    }

    public static function getStandardPort(string $scheme) : int
    {
        return isset(self::$standardPorts[$scheme])
            ? self::$standardPorts[$scheme]
            : 0;
    }

    public static function parseString(string $string) : UriInterface
    {
        $parsed = parse_url($string);

        $scheme     = isset($parsed['scheme'])   ? $parsed['scheme'] : '';
        $username   = isset($parsed['user'])     ? $parsed['user'] : '';
        $password   = isset($parsed['pass'])     ? $parsed['pass'] : '';
        $host       = isset($parsed['host'])     ? $parsed['host'] : '';
        $port       = isset($parsed['port'])     ? (int) $parsed['port'] : NULL;
        $path       = isset($parsed['path'])     ? $parsed['path'] : '';
        $query      = isset($parsed['query'])    ? $parsed['query'] : '';
        $fragment   = isset($parsed['fragment']) ? $parsed['fragment'] : '';

        return new self($scheme, $username, $password, $host, $port, $path, $query, $fragment);        
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

    protected function validateScheme($scheme) 
    {
        if (! is_string($scheme)) {
            throw new \InvalidArgumentException('Scheme must be a string');
        }

        if (!empty($scheme) && !in_array(strtolower($scheme), array_keys(self::$standardPorts))) {
            throw new \InvalidArgumentException('Unsupported scheme: ' . $scheme);
        }

        return true;
    }

    protected function validateHost($host) 
    {
        if (! is_string($host)) {
            throw new \InvalidArgumentException('Host must be a string');
        }

        if (!empty($host) && !self::isValidHost($host)) {
            throw new \InvalidArgumentException('Invalid host: ' . $host);
        }

        return true;
    }

    protected function validatePort($port) 
    {
        if (!is_null($port) && !is_int($port)) {
            throw new \InvalidArgumentException('Port must be null or an integer');
        }

        if (is_int($port) && ($port < 1 || $port > 65535)) {
            throw new \InvalidArgumentException('Port must be in the 1 - 65535 range');
        }

        return true;
    }

    protected function validatePath($path) 
    {
        if (! is_string($path)) {
            throw new \InvalidArgumentException('Path must be a string');
        }

        return true;
    }

    protected function validateQuery($query) 
    {
        if (! is_string($query)) {
            throw new \InvalidArgumentException('Query must be a string');
        }

        return true;
    }

    protected function validateFragment($fragment) 
    {
        if (! is_string($fragment)) {
            throw new \InvalidArgumentException('Fragment must be a string');
        }

        return true;
    }

    protected function getConstructorParameters() 
    {
        return [
            'scheme'   => $this->scheme,
            'username' => $this->username,
            'password' => $this->password,
            'host'     => $this->host,
            'port'     => $this->port,
            'path'     => $this->path,
            'query'    => $this->query,
            'fragment' => $this->fragment
        ];
    }
}
