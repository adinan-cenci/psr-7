# PSR-7

This is an implementation of the [PSR-7: HTTP message interfaces](https://www.php-fig.org/psr/psr-7/) specification.

Why did I reinvented the wheel when there are so many of those already ?

Because I wanted, that's why.

<br><br><br>

## Niceties

Besides implementing PSR-7, there is other methods to make things a little easier.

**ServerRequest**  

```php
$fallback = null;

$request->post($name, $fallback);
$request->get($name, $fallback);
$request->cookie($name, $fallback);
$request->server($name, $fallback);
```

Methods to quickly retrieve data from the request, think of the traditional globals: `$_POST`, `$_GET`, `$_COOKIE` and `$_SERVER`.

**Response**

```php
$response = $response->withAddedCookie(
    'cookie_name',
    $cookieValue,
    $twentyFourHours,         // Max age.
    $now + $twentyFourHours,  // Expiration, accepts DateTime objects as well.
    '/',                      // Path.
    'my-domain.com',          // Domain.
    true,                     // Secure.
    true,                     // Http only.
    'Strict'                  // Same site.
);
```

A method to help compose cookies.

<br><br><br>

## License

MIT
