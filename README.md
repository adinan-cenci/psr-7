# PSR-7

This is an implementation of the [PSR-7: HTTP message interfaces](https://www.php-fig.org/psr/psr-7/) specification.

Why did I reinvented the wheel when there are so many of those already ?

Because I wanted, that's why.

<br><br><br>

## Niceties

Besides implementing PSR-7, there is other methods to make things a little easier.

**ServerRequest**  
`::post($name, $default = null)`  
`::get($name, $default = null)`  
`::cookie($name, $default = null)`  
`::server($name, $default = null)`

Methods to quickly retrieve data from the request, think of the traditional globals: `$_POST`, `$_GET`, `$_COOKIE` and `$_SERVER`.



**Response**  
`::withAddedCookie(string $name, string $value, ?int $expires = null, string $path = '', string $domain = '', bool $secure = false, bool $httpOnly = false)`

it works very similarly to `setcookie()`.

<br><br><br>

## License

MIT