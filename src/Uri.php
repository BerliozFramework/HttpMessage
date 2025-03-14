<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2021 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Http\Message;

use InvalidArgumentException;
use JsonSerializable;
use Psr\Http\Message\UriInterface;
use Stringable;

/**
 * Class Uri.
 */
class Uri implements UriInterface, Stringable, JsonSerializable
{
    /**
     * Uri constructor.
     *
     * @param string $scheme Scheme of uri
     * @param string $host Host of uri
     * @param int|null $port Port of uri
     * @param string $path Path  of uri
     * @param string $query Query  of uri
     * @param string $fragment Fragment of uri
     * @param string $user User of uri
     * @param string|null $password Password of uri
     */
    public function __construct(
        protected string $scheme = '',
        protected string $host = '',
        protected ?int $port = null,
        protected string $path = '',
        protected string $query = '',
        protected string $fragment = '',
        protected string $user = '',
        protected ?string $password = null
    ) {
    }

    /**
     * Create Uri.
     *
     * @param Uri|string $uri
     * @param Uri|string|null $ref
     *
     * @return static
     */
    public static function create(UriInterface|string $uri, UriInterface|string|null $ref = null): static
    {
        is_string($uri) && $uri = static::createFromString($uri);
        is_string($ref) && $ref = static::createFromString($ref);

        if (!empty($uri->getHost()) || null === $ref) {
            if (empty($uri->getScheme())) {
                return $uri->withScheme($ref?->getScheme() ?? '');
            }

            if ($uri instanceof static) {
                return $uri;
            }

            // Convert any UriInterface into static
            $userInfo = explode(':', $uri->getUserInfo(), 2);

            return new static(
                scheme: $uri->getScheme(),
                host: $uri->getHost(),
                port: $uri->getPort(),
                path: $uri->getPath(),
                query: $uri->getQuery(),
                fragment: $uri->getFragment(),
                user: rawurldecode($userInfo[0] ?? ''),
                password: rawurldecode($userInfo[1] ?? ''),
            );
        }

        $userInfo = explode(':', $ref->getUserInfo(), 2);

        return new static(
            scheme: $ref->getScheme(),
            host: $ref->getHost(),
            port: $ref->getPort(),
            path: b_resolve_absolute_path($ref->getPath(), $uri->getPath()),
            query: $uri->getQuery(),
            fragment: $uri->getFragment(),
            user: rawurldecode($userInfo[0] ?? ''),
            password: rawurldecode($userInfo[1] ?? ''),
        );
    }

    /**
     * Create Uri with string
     *
     * @param string $str
     *
     * @return static
     */
    public static function createFromString(string $str): static
    {
        $parsedUrl = parse_url(trim($str));

        if (false === $parsedUrl) {
            throw new InvalidArgumentException('Invalid URI');
        }

        return new self(
            scheme: $parsedUrl['scheme'] ?? '',
            host: $parsedUrl['host'] ?? '',
            port: $parsedUrl['port'] ?? null,
            path: $parsedUrl['path'] ?? '',
            query: $parsedUrl['query'] ?? '',
            fragment: $parsedUrl['fragment'] ?? '',
            user: rawurldecode($parsedUrl['user'] ?? ''),
            password: rawurldecode($parsedUrl['pass'] ?? '')
        );
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string The URI scheme.
     */
    public function getScheme(): string
    {
        return strtolower($this->scheme) ?? '';
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority(): string
    {
        $authority = '';

        // User info
        if (!empty($userInfo = $this->getUserInfo())) {
            $authority .= $userInfo . '@';
        }

        // Host
        $authority .= $this->getHost();

        // Port
        if (null !== ($port = $this->getPort())) {
            $authority .= ':' . $port;
        }

        return $authority;
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo(): string
    {
        $userInfo = '';

        if (strlen($this->user ?? '') > 0) {
            $userInfo =
                rawurlencode($this->user) .
                (strlen($this->password ?? '') > 0 ? ':' . rawurlencode($this->password) : '');
        }

        return $userInfo;
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     */
    public function getHost(): string
    {
        return strtolower($this->host);
    }

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The URI port.
     */
    public function getPort(): ?int
    {
        switch ($this->getScheme()) {
            case 'http':
                if ($this->port != 80) {
                    return $this->port;
                }

                return null;
            case 'https':
                if ($this->port != 443) {
                    return $this->port;
                }

                return null;
            default:
                return $this->port ?? null;
        }
    }

    /**
     * Retrieve the path component of the URI.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string The URI path.
     */
    public function getPath(): string
    {
        return $this->path ?? '';
    }

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method MUST return an empty string.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string The URI query string.
     */
    public function getQuery(): string
    {
        return $this->query ?? '';
    }

    /**
     * Get query value in the query string of the URI.
     *
     * @param string $name Name of variable
     * @param mixed $default Default value
     *
     * @return mixed Query value in the query string of the URI.
     */
    public function getQueryValue(string $name, mixed $default = null): mixed
    {
        $query = b_parse_str($this->query);

        return $query[$name] ?? $default;
    }

    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method MUST return an empty string.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string The URI fragment.
     */
    public function getFragment(): string
    {
        return $this->fragment ?? '';
    }

    /**
     * Return an instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     *
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     *
     * @return static A new instance with the specified scheme.
     * @throws InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme): static
    {
        $clone = clone $this;
        $clone->scheme = $scheme;

        return $clone;
    }

    /**
     * Return an instance with the specified user information.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param string $user The user name to use for authority.
     * @param string|null $password The password associated with $user.
     *
     * @return static A new instance with the specified user information.
     */
    public function withUserInfo(string $user, ?string $password = null): static
    {
        $clone = clone $this;
        $clone->user = $user;
        $clone->password = $password;

        return $clone;
    }

    /**
     * Return an instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host The hostname to use with the new instance.
     *
     * @return static A new instance with the specified host.
     * @throws InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host): static
    {
        $clone = clone $this;
        $clone->host = $host;

        return $clone;
    }

    /**
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port The port to use with the new instance; a null value
     *                       removes the port information.
     *
     * @return static A new instance with the specified port.
     * @throws InvalidArgumentException for invalid ports.
     */
    public function withPort($port): static
    {
        $clone = clone $this;
        $clone->port = $port;

        return $clone;
    }

    /**
     * Return an instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified path.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * If the path is intended to be domain-relative rather than path relative then
     * it must begin with a slash ("/"). Paths not starting with a slash ("/")
     * are assumed to be relative to some base path known to the application or
     * consumer.
     *
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param string $path The path to use with the new instance.
     *
     * @return static A new instance with the specified path.
     * @throws InvalidArgumentException for invalid paths.
     */
    public function withPath($path): static
    {
        $clone = clone $this;
        $clone->path = $path;

        return $clone;
    }

    /**
     * Return an instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query The query string to use with the new instance.
     *
     * @return static A new instance with the specified query string.
     * @throws InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query): static
    {
        $clone = clone $this;
        $clone->query = $query;

        return $clone;
    }

    /**
     * Return an instance with the added query string.
     *
     * @param string $query
     *
     * @return static A new instance with the added query string.
     */
    public function withAddedQuery(string $query): static
    {
        $original = b_parse_str($this->query);
        $query = b_parse_str($query);
        $query = array_merge_recursive($original, $query);
        $query = array_filter($query, fn($value) => null !== $value);

        $clone = clone $this;
        $clone->query = http_build_query($query, arg_separator: '&', encoding_type: PHP_QUERY_RFC3986);

        return $clone;
    }

    /**
     * Return an instance without the specified query string name.
     *
     * @param string $name
     *
     * @return static A new instance without the specified query string name.
     */
    public function withoutQuery(string $name): static
    {
        $query = b_parse_str($this->query);
        unset($query[$name]);

        $clone = clone $this;
        $clone->query = http_build_query($query, arg_separator: '&', encoding_type: PHP_QUERY_RFC3986);

        return $clone;
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment The fragment to use with the new instance.
     *
     * @return static A new instance with the specified fragment.
     */
    public function withFragment($fragment): static
    {
        $clone = clone $this;
        $clone->fragment = $fragment;

        return $clone;
    }

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    public function __toString(): string
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $path = $this->getPath();
        $query = $this->getQuery();
        $query = (strlen($query) > 0 ? '?' . $query : '');
        $fragment = $this->getFragment();
        $fragment = (strlen($fragment) > 0 ? '#' . $fragment : '');

        if (empty($path)) {
            if (!empty($query) || !empty($fragment)) {
                $path = '/';
            }
        }

        $uri = (!empty($authority) ? (!empty($scheme) ? $scheme . ':' : '') . '//' . $authority : '') .
            $path . $query . $fragment;

        return strtr($uri, ' ', '+');
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): string
    {
        return (string)$this;
    }
}