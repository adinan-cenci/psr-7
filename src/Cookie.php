<?php 
namespace AdinanCenci\Psr7;

// This is not part of the psr7 specification.
class Cookie 
{
    use FunctionalInstantiationTrait;

    protected string $name;
    protected string $value;
    protected ?int $maxAge = null;
    protected $expires = null;
    protected string $path = '';
    protected string $domain = '';
    protected bool $secure = false;
    protected bool $httpOnly = false;
    protected string $sameSite = '';

    public function __construct(string $name, string $value, ?int $maxAge = null, $expires = null, string $path = '', string $domain = '', bool $secure = false, bool $httpOnly = false, string $sameSite = '') 
    {
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

    public function __toString() 
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

    public function withName(string $name) 
    {
        $this->validateName($name);

        return $this->instantiate(['name' => $name]);
    }

    public function withValue(string $value) 
    {
        return $this->instantiate(['value' => $value]);
    }

    public function withMaxAge($maxAge) 
    {
        return $this->instantiate(['maxAge' => $maxAge]);
    }

    public function withExpires(int $expires) 
    {
        $this->validateExpires($expires);

        return $this->instantiate(['expires' => $expires]);
    }

    public function withDomain(string $domain) 
    {
        $this->validateDomain($domain);

        return $this->instantiate(['domain' => $domain]);
    }

    public function withPath(string $path) 
    {
        return $this->instantiate(['path' => $path]);
    }

    public function withSameSite(string $sameSite) 
    {
        $this->validateSameSite($sameSite);

        return $this->instantiate(['sameSite' => $sameSite]);
    }

    public function withSecure(bool $secure) 
    {
        return $this->instantiate(['secure' => $secure]);
    }

    public function withHttpOnly(bool $httpOnly) 
    {
        return $this->instantiate(['httpOnly' => $httpOnly]);
    }

    protected function setExpires($expires) 
    {
        if (is_null($expires)) {
            $this->expires = $expires;
            return;
        }

        $this->expires = $expires instanceof \DateTime
            ? clone $expires
            : (new \DateTime())->setTimestamp($expires);

        $this->expires->setTimezone(new \DateTimeZone('GMT'));
    }

    protected function formatExpires($expires) : string 
    {
        return $expires->format('D, d m Y H:i:s e');
    }

    protected function validateName(string $name) 
    {
        if (empty($name) || preg_match('/[()<>,;:{}=\]\[?"\\\]/', $name)) {
            throw new \InvalidArgumentException('Invalid name');
        }

        return true;
    }

    protected function validateExpires($expires) 
    {
        if ($expires instanceof \DateTime || is_int($expires) || is_null($expires)) {
            return true;
        }

        throw new \InvalidArgumentException('Expire should be a timestamp or DateTime instance');
    }

    protected function validateSameSite(string $sameSite) 
    {
        if (!in_array($sameSite, ['', 'Strict', 'Lax', 'None'])) {
            throw new \InvalidArgumentException('Invalid same site policy: ' . $sameSite);
        }

        return true;
    }

    protected function validateDomain($domain) 
    {
        if (!empty($domain) && !Uri::isValidHost($domain)) {
            throw new \InvalidArgumentException('Invalid domain: ' . $domain);
        }
    }

    protected function getConstructorParameters() 
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
