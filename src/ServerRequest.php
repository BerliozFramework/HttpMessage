<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Http\Message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class ServerRequest.
 *
 * @package Berlioz\Http\Message
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /** @var array Cookies */
    private $cookies;
    /** @var array Server parameters */
    private $serverParams;
    /** @var \Psr\Http\Message\UploadedFileInterface[] Uploaded files */
    private $uploadedFiles;
    /** @var array Query parameters */
    private $queryParams;
    /** @var array Attributes */
    private $attributes;

    public function __construct(
        $method,
        UriInterface $uri,
        array $headers,
        array $cookies,
        array $serverParams,
        StreamInterface $body,
        array $uploadedFiles = [],
        array $attributes = []
    ) {
        parent::__construct($method, $uri);

        $this->cookies = $cookies;
        $this->serverParams = $serverParams;
        $this->body = $body;
        $this->uploadedFiles = $uploadedFiles;
        $this->attributes = $attributes;

        $this->headers = [];
        foreach ($headers as $name => $value) {
            $name = ucwords(strtolower($name), ' -_');
            $this->headers[$name] = (array)$value;
        }
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Retrieve server parameter.
     *
     * @param string $name Param name
     * @param mixed $default Default value to return if the param does not exist.
     *
     * @return mixed|null
     */
    public function getServerParam(string $name, $default = null)
    {
        $serverParams = $this->getServerParams();

        return $serverParams[$name] ?? $default;
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array
     */
    public function getCookieParams()
    {
        return $this->cookies;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * This method MUST NOT update the related Cookie header of the request
     * instance, nor related values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated cookie values.
     *
     * @param array $cookies Array of key/value pairs representing cookies.
     *
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $clone = clone $this;
        $clone->cookies = $cookies;

        return $clone;
    }

    /**
     * Retrieve query string arguments.
     *
     * Retrieves the deserialized query string arguments, if any.
     *
     * Note: the query params might not be in sync with the URI or server
     * params. If you need to ensure you are only getting the original
     * values, you may need to parse the query string from `getUri()->getQuery()`
     * or from the `QUERY_STRING` server param.
     *
     * @return array
     */
    public function getQueryParams()
    {
        if (null !== $this->queryParams) {
            return $this->queryParams;
        }

        if (null !== $this->getUri()) {
            parse_str($this->getUri()->getQuery(), $this->queryParams);

            return $this->queryParams;
        }

        return [];
    }

    /**
     * Retrieve query string argument.
     *
     * @param string $name Query param name
     * @param mixed $default Default value to return if the param does not exist.
     *
     * @return mixed|null
     */
    public function getQueryParam(string $name, $default = null)
    {
        $queryParams = $this->getQueryParams();

        return $queryParams[$name] ?? $default;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * These values SHOULD remain immutable over the course of the incoming
     * request. They MAY be injected during instantiation, such as from PHP's
     * $_GET superglobal, or MAY be derived from some other value such as the
     * URI. In cases where the arguments are parsed from the URI, the data
     * MUST be compatible with what PHP's parse_str() would return for
     * purposes of how duplicate query parameters are handled, and how nested
     * sets are handled.
     *
     * Setting query string arguments MUST NOT change the URI stored by the
     * request, nor the values in the server params.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated query string arguments.
     *
     * @param array $query Array of query string arguments, typically from
     *                     $_GET.
     *
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $clone = clone $this;
        $clone->queryParams = $query;

        return $clone;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface.
     *
     * These values MAY be prepared from $_FILES or the message body during
     * instantiation, or MAY be injected via withUploadedFiles().
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *     array MUST be returned if no data is present.
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles ?? [];
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
     *
     * @return static
     * @throws \InvalidArgumentException if an invalid structure is provided.
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $clone = clone $this;
        $clone->uploadedFiles = $uploadedFiles;

        return $clone;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes ?? [];
    }

    /**
     * Return an instance with the specified derived request attributes.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attributes.
     *
     * @param array $attributes Attributes.
     *
     * @return static
     * @see getAttributes()
     *
     */
    public function withAttributes(array $attributes)
    {
        $clone = clone $this;
        $clone->attributes = $attributes;

        return $clone;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     *
     * @return mixed
     * @see getAttributes()
     *
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes[$name] ?? $default ?? null;
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     *
     * @return static
     * @see getAttributes()
     *
     */
    public function withAttribute($name, $value)
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @param string $name The attribute name.
     *
     * @return static
     * @see getAttributes()
     *
     */
    public function withoutAttribute($name)
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);

        return $clone;
    }

    /**
     * Is AJAX request ?
     *
     * @return bool
     */
    public function isAjaxRequest()
    {
        if ($this->hasHeader('AjaxRequest')) {
            return true;
        }

        if ($this->getServerParam('HTTP_X_REQUESTED_WITH', '') == 'xmlhttprequest') {
            return true;
        }

        return false;
    }
}