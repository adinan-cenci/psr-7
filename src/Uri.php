<?php

namespace AdinanCenci\Psr7;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    use FunctionalInstantiationTrait;

    /**
     * Scheme of the uri ( https, ftp, file, etc ).
     *
     * @var string
     */
    protected $scheme = '';

    /**
     * Username.
     *
     * @var string
     */
    protected $username = '';

    /**
     * Password.
     *
     * @var string
     */
    protected $password = '';

    /**
     * Host, domain name or IP.
     *
     * @var string
     */
    protected $host = '';

    /**
     * Port.
     *
     * @var int|null
     */
    protected $port = null;

    /**
     * Path.
     *
     * @var string
     */
    protected $path = '';

    /**
     * Query string.
     *
     * @var string
     */
    protected $query = '';

    /**
     * Fragment.
     *
     * @var string
     */
    protected $fragment = '';

    /**
     * List of default ports for different schemes.
     *
     * @var array
     */
    protected static $standardPorts = [
        'http'  => 80,
        'https' => 443,
        'ftp'   => 21,
        'smtp'  => 587,
        'imap'  => 993,
    ];

    /**
     * Constructor.
     *
     * @param string $scheme
     *   Scheme of the uri ( https, ftp, file, etc ).
     * @param string $username
     *   Username.
     * @param string $password
     *   Password.
     * @param string $host
     *   Host, domain name or IP.
     * @param int|null $port
     *   Port.
     * @param string $path
     *   Path.
     * @param string $query
     *   Query string.
     * @param string $fragment
     *   Fragment.
     */
    public function __construct(
        $scheme = '',
        $username = '',
        $password = '',
        $host = '',
        $port = null,
        $path = '',
        $query = '',
        $fragment = ''
    ) {
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

    /**
     * {@inheritdoc}
     */
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
        } elseif ($path && !$authority) {
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

    /**
     * {@inheritdoc}
     */
    public function getScheme(): string
    {
        $scheme = (string) $this->scheme;
        $scheme = trim($scheme, ':');
        $scheme = strtolower($scheme);
        return $scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort(): ?int
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

    /**
     * {@inheritdoc}
     */
    public function getUserInfo(): string
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

    /**
     * {@inheritdoc}
     */
    public function getHost(): string
    {
        $host = $this->host;

        if ($host == '') {
            return '';
        }

        return strtolower($host);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority(): string
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

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        if (empty($this->path)) {
            return '';
        }

        // Straight up stolen from slimphp/Slim-Psr7
        // Do not encode \w, _, -, & etc nor % encoded characters.
        return $this->urlEncode('/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/', $this->path);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
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

    /**
     * {@inheritdoc}
     */
    public function getFragment(): string
    {
        $fragment = $this->fragment;

        if ($fragment == '') {
            return '';
        }

        // You know the drill by now
        // Do not encode \w, _, - & etc nor % encoded characters.
        return $this->urlEncode('/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/', $fragment);
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme(string $scheme): UriInterface
    {
        $this->validateScheme($scheme);
        return $this->instantiate(['scheme' => $scheme]);
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($username, $password = null): UriInterface
    {
        $password = (string) $password;
        return $this->instantiate(['username' => $username, 'password' => $password]);
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host): UriInterface
    {
        $this->validateHost($host);
        return $this->instantiate(['host' => $host]);
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port): UriInterface
    {
        $this->validatePort($port);
        return $this->instantiate(['port' => $port]);
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path): UriInterface
    {
        $this->validatePath($path);
        return $this->instantiate(['path' => $path]);
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query): UriInterface
    {
        $this->validateQuery($query);
        return $this->instantiate(['query' => $query]);
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment): UriInterface
    {
        $this->validateFragment($fragment);
        return $this->instantiate(['fragment' => $fragment]);
    }

    /**
     * Checks if a given port is the standard one for the specified scheme.
     *
     * @param int $port
     *   Network port.
     * @param string scheme
     *   Network scheme.
     *
     * @return bool
     *   True if port matches the scheme.
     */
    public static function isStandardPort(int $port, string $scheme): bool
    {
        return isset(self::$standardPorts[$scheme])
            ? self::$standardPorts[$scheme] == $port
            : false;
    }

    /**
     * Validates if a string is a valid host.
     *
     * Hostname or IP number.
     *
     * @param string $host
     *   Host.
     *
     * @return bool
     *   Returns true if it is valid.
     */
    public static function isValidHost($host): bool
    {
        return self::isValidHostName($host) || self::isValidIp($host);
    }

    /**
     * Validates if a string is a valid hostname.
     *
     * @param string $hostname
     *   Hostname.
     *
     * @return bool
     *   Returns true if it is valid.
     */
    public static function isValidHostName($hostname): bool
    {
        return (bool) filter_var($hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
    }

    /**
     * Validates if a string is a valid IP.
     *
     * @param string $ip
     *   IP number.
     *
     * @return bool
     *   Returns true if it is valid.
     */
    public static function isValidIp($ip): bool
    {
        return (bool) filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * Returns the standard port for a given scheme.
     *
     * @param string $scheme
     *   Scheme.
     *
     * @return bool
     *   The port number, 0 if it does not recognize the scheme.
     */
    public static function getStandardPort(string $scheme): int
    {
        return isset(self::$standardPorts[$scheme])
            ? self::$standardPorts[$scheme]
            : 0;
    }

    /**
     * Parses a string into an Uri object.
     *
     * @param string $string
     *   An URI.
     *
     * @throws \InvalidArgumentException
     *
     * @return Psr\Http\Message\UriInterface
     *   The parsed object.
     */
    public static function parseString(string $string): UriInterface
    {
        $parsed = parse_url($string);

        $scheme     = isset($parsed['scheme'])   ? $parsed['scheme'] : '';
        $username   = isset($parsed['user'])     ? $parsed['user'] : '';
        $password   = isset($parsed['pass'])     ? $parsed['pass'] : '';
        $host       = isset($parsed['host'])     ? $parsed['host'] : '';
        $port       = isset($parsed['port'])     ? (int) $parsed['port'] : null;
        $path       = isset($parsed['path'])     ? $parsed['path'] : '';
        $query      = isset($parsed['query'])    ? $parsed['query'] : '';
        $fragment   = isset($parsed['fragment']) ? $parsed['fragment'] : '';

        return new self($scheme, $username, $password, $host, $port, $path, $query, $fragment);
    }

    /**
     * URL encodes a string.
     *
     * @param string $regex
     *   Regex pattern to target specific parts of the string for encoding.
     * @param string $string
     *   The string to be encoded.
     *
     * @return string
     *   The encoded string.
     */
    protected function urlEncode(string $regex, string $string): string
    {
        return preg_replace_callback($regex, function ($match) {
            return rawurlencode($match[0]);
        }, $string);
    }

    /**
     * Validates if $scheme is a valid URI scheme.
     *
     * @param string $scheme.
     *   URI scheme.
     *
     * @throws \InvalidArgumentException
     *   If it isn't valid.
     *
     * @return bool
     *   True if it is valid.
     */
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

    /**
     * Validates if $host is a valid host.
     *
     * @param string $host.
     *   Host, domain name or IP.
     *
     * @throws \InvalidArgumentException
     *   If it isn't valid.
     *
     * @return bool
     *   True if it is valid.
     */
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

    /**
     * Validates if $port is a valid port.
     *
     * @param int $port.
     *   Port.
     *
     * @throws \InvalidArgumentException
     *   If it isn't valid.
     *
     * @return bool
     *   True if it is valid.
     */
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

    /**
     * Validates if $path is a valid path.
     *
     * @param string $path.
     *   Path.
     *
     * @throws \InvalidArgumentException
     *   If it isn't valid.
     *
     * @return bool
     *   True if it is valid.
     */
    protected function validatePath($path)
    {
        if (! is_string($path)) {
            throw new \InvalidArgumentException('Path must be a string');
        }

        return true;
    }

    /**
     * Validates if $query is a valid query.
     *
     * @param string $query.
     *   Query.
     *
     * @throws \InvalidArgumentException
     *   If it isn't valid.
     *
     * @return bool
     *   True if it is valid.
     */
    protected function validateQuery($query)
    {
        if (! is_string($query)) {
            throw new \InvalidArgumentException('Query must be a string');
        }

        return true;
    }

    /**
     * Validates if $fragment is a valid fragment.
     *
     * @param string $fragment.
     *   Fragment.
     *
     * @throws \InvalidArgumentException
     *   If it isn't valid.
     *
     * @return bool
     *   True if it is valid.
     */
    protected function validateFragment($fragment)
    {
        if (! is_string($fragment)) {
            throw new \InvalidArgumentException('Fragment must be a string');
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
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
