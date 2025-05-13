<?php

namespace AdinanCenci\Psr7;

/**
 * Represents a cookie.
 *
 * This is not part of the PSR-7 specification, but a feature of the library.
 */
class Cookie
{
    use FunctionalInstantiationTrait;

    /**
     * The name of the cookie.
     *
     * @var string
     */
    protected string $name;

    /**
     * The value of the cookie.
     *
     * @var string
     */
    protected string $value;

    /**
     * Max age of the cookie in seconds.
     *
     * @var int|null
     */
    protected ?int $maxAge = null;

    /**
     * The date the cookie is supposed to expire.
     *
     * @var \DateTime|null
     */
    protected ?\DateTime $expires = null;

    /**
     * Specific URI path the cookie should be accessible to.
     *
     * @var string
     */
    protected string $path = '';

    /**
     * Domain the cookies should be accessible to.
     *
     * @var string
     */
    protected string $domain = '';

    /**
     * Whether the cookie should be sent over SSL only.
     *
     * @var bool
     */
    protected bool $secure = false;

    /**
     * Whether the cookies should be accessible through java-script.
     *
     * @var bool
     */
    protected bool $httpOnly = false;

    /**
     * Cross-site setting.
     *
     * @var string
     */
    protected string $sameSite = '';

    /**
     * Constructor.
     *
     * @param string $name
     *   The name of the cookie.
     * @param string $value
     *   The value of the cookie.
     * @param int|null
     *   Max age of the cookie in seconds.
     * @param \DateTime|int|null
     *   The date the cookie is supposed to expire.
     *   It accepts a timestamp or a \DateTime object.
     * @param string
     *   Specific URI path the cookie should be accessible to.
     * @param string
     *   Domain the cookies should be accessible to.
     * @param bool
     *   Whether the cookie should be sent over SSL only.
     * @param bool
     *   Whether the cookies should be accessible through java-script.
     * @param string
     *   Cross-site setting.
     */
    public function __construct(
        string $name,
        string $value,
        ?int $maxAge = null,
        mixed $expires = null,
        string $path = '',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = false,
        string $sameSite = ''
    ) {
        $this->validateName($name);
        $this->validateExpires($expires);
        $this->validateSameSite($sameSite);
        $this->validateDomain($domain);

        $this->name     = $name;
        $this->value    = $value;
        $this->maxAge   = $maxAge;
        $this->setExpires($expires);
        $this->path     = $path;
        $this->domain   = $domain;
        $this->secure   = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $sameSite;
    }

    /**
     * Renders the cookie as a HTTP header.
     *
     * @return string
     *   The cookies as HTTP header.
     */
    public function __toString(): string
    {
        $header = $this->name . '=' . $this->value;

        if ($this->maxAge !== null) {
            $header .= '; MaxAge=' . $this->maxAge;
        }

        if ($this->expires) {
            $header .= '; Expires=' . $this->formatExpires($this->expires);
        }

        if ($this->domain) {
            $header .= '; Domain=' . $this->domain;
        }

        if ($this->path) {
            $header .= '; Path=' . $this->path;
        }

        if ($this->sameSite) {
            $header .= '; SameSite=' . $this->sameSite;
        }

        if ($this->secure) {
            $header .= '; Secure';
        }

        if ($this->httpOnly) {
            $header .= '; HttpOnly';
        }

        return $header;
    }

    /**
     * Returns an instance with the specified name.
     *
     * @param string $name
     *   The name of the cookie.
     *
     * @return static
     *   A new instance.
     */
    public function withName(string $name)
    {
        $this->validateName($name);

        return $this->instantiate(['name' => $name]);
    }

    /**
     * Returns an instance with the specified value.
     *
     * @param string $name
     *   The value of the cookie.
     *
     * @return static
     *   A new instance.
     */
    public function withValue(string $value)
    {
        return $this->instantiate(['value' => $value]);
    }

    /**
     * Returns an instance with the specified max age.
     *
     * @param int $maxAge
     *   Max age of the cookie in seconds.
     *
     * @return static
     *   A new instance.
     */
    public function withMaxAge(?int $maxAge = null)
    {
        return $this->instantiate(['maxAge' => $maxAge]);
    }

    /**
     * Returns an instance with the specified expiration date.
     *
     * @param int $expires
     *   The date the cookie is supposed to expire.
     *
     * @return static
     *   A new instance.
     */
    public function withExpires($expires)
    {
        $this->validateExpires($expires);

        return $this->instantiate(['expires' => $expires]);
    }

    /**
     * Returns a new instance with the specified path setting.
     *
     * @param string $path
     *   Specific URL path the cookie should be accessible to.
     *
     * @return static
     *   A new instance.
     */
    public function withPath(string $path = '')
    {
        return $this->instantiate(['path' => $path]);
    }

    /**
     * Returns a new instance with the specified domain.
     *
     * @param string $domain
     *   Domain the cookies should be accessible to.
     *
     * @return static
     *   A new instance.
     */
    public function withDomain(string $domain = '')
    {
        $this->validateDomain($domain);

        return $this->instantiate(['domain' => $domain]);
    }

    /**
     * Returns a new instance with the specified secure setting.
     *
     * @param bool $secure
     *   Whether the cookie should be sent over ssl only.
     *
     * @return static
     *   A new instance.
     */
    public function withSecure(bool $secure = false)
    {
        return $this->instantiate(['secure' => $secure]);
    }

    /**
     * Returns a new instance with the specified httpOnly setting.
     *
     * @param bool $httpOnly
     *   Whether the cookies should be accessed through java-script.
     *
     * @return static
     *   A new instance.
     */
    public function withHttpOnly(bool $httpOnly = false)
    {
        return $this->instantiate(['httpOnly' => $httpOnly]);
    }

    /**
     * Returns a new instance with the specified sameSite setting.
     *
     * @param string $sameSite
     *   Cross-site setting.
     *
     * @return static
     *   A new instance.
     */
    public function withSameSite(string $sameSite = '')
    {
        $this->validateSameSite($sameSite);

        return $this->instantiate(['sameSite' => $sameSite]);
    }

    /**
     * Sets the expiration time.
     *
     * @param null|int|\DateTime $expires
     *   The date the cookie should expire.
     */
    protected function setExpires($expires): void
    {
        if (is_null($expires)) {
            $this->expires = null;
            return;
        }

        $this->expires = $expires instanceof \DateTime
            ? clone $expires
            : (new \DateTime())->setTimestamp($expires);

        $this->expires->setTimezone(new \DateTimeZone('GMT'));
    }

    /**
     * Formats a date object into a string.
     *
     * @param \DateTime $expires
     *   Date time object.
     *
     * @return string
     *   The formatted date.
     */
    protected function formatExpires(\DateTime $expires): string
    {
        return $expires->format('D, d M Y H:i:s e');
    }

    /**
     * Validates if a string is a valid cookie name.
     *
     * @param string $name
     *   The would be cookie name.
     *
     * @throws \InvalidArgumentException
     *   If it isn't valid.
     *
     * @return bool
     *   True if it is valid.
     */
    protected function validateName(string $name)
    {
        if (empty($name) || preg_match('/[()<>,;:{}=\]\[?"\\\]/', $name)) {
            throw new \InvalidArgumentException('Invalid name');
        }

        return true;
    }

    /**
     * Validates if $expires is a valid date.
     *
     * It must be an integer ( timestamp ) or a \DateTime object.
     *
     * @param int\DateTime $expires
     *   Expiration time.
     *
     * @throws \InvalidArgumentException
     *   If it isn't valid.
     *
     * @return bool
     *   True if it is valid.
     */
    protected function validateExpires($expires)
    {
        if ($expires instanceof \DateTime || is_int($expires) || is_null($expires)) {
            return true;
        }

        throw new \InvalidArgumentException('Expire should be a timestamp or DateTime instance');
    }

    /**
     * Validates if $sameSite setting is valid.
     *
     * @param string $sameSite.
     *   Same site setting.
     *
     * @throws \InvalidArgumentException
     *   If it isn't valid.
     *
     * @return bool
     *   True if it is valid.
     */
    protected function validateSameSite(string $sameSite)
    {
        if (!in_array($sameSite, ['', 'Strict', 'Lax', 'None'])) {
            throw new \InvalidArgumentException('Invalid same site policy: ' . $sameSite);
        }

        return true;
    }

    /**
     * Validates if $domain is a valid domain.
     *
     * @param string $domain.
     *   A domain.
     *
     * @throws \InvalidArgumentException
     *   If it isn't valid.
     *
     * @return bool
     *   True if it is valid.
     */
    protected function validateDomain(string $domain = '')
    {
        if (!empty($domain) && !Uri::isValidHost($domain)) {
            throw new \InvalidArgumentException('Invalid domain: ' . $domain);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConstructorParameters(): array
    {
        return [
            'name'      => $this->name,
            'value'     => $this->value,
            'maxAge'    => $this->maxAge,
            'expires'   => $this->expires,
            'path'      => $this->path,
            'domain'    => $this->domain,
            'secure'    => $this->secure,
            'httpOnly'  => $this->httpOnly,
            'sameSite'  => $this->sameSite
        ];
    }
}
