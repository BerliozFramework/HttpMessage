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

namespace Berlioz\Http\Message\Factory;

use Berlioz\Http\Message\ServerRequest;
use Berlioz\Http\Message\Stream\PhpInputStream;
use Berlioz\Http\Message\UploadedFile;
use Berlioz\Http\Message\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Trait ServerRequestFactoryTrait.
 */
trait ServerRequestFactoryTrait
{
    use UriFactoryTrait;

    /**
     * Create a new server request.
     *
     * Note that server parameters are taken precisely as given - no parsing/processing
     * of the given values is performed. In particular, no attempt is made to
     * determine the HTTP method or URI, which must be provided explicitly.
     *
     * @param string $method The HTTP method associated with the request.
     * @param UriInterface|string $uri The URI associated with the request.
     * @param array $serverParams An array of Server API (SAPI) parameters with
     *                                          which to seed the generated request instance.
     *
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (is_string($uri)) {
            $uri = $this->createUri($uri);
        }

        return new ServerRequest($method, $uri, [], [], $serverParams);
    }

    /**
     * Get headers from PHP globals.
     *
     * @return array
     */
    protected function getHeadersFromGlobals(): array
    {
        // Get all headers
        if (function_exists('\getallheaders')) {
            return getallheaders() ?: [];
        }

        $headers = [];

        $serverVars = $_SERVER;
        $serverVars['HTTP_CONTENT_TYPE'] = $serverVars['HTTP_CONTENT_TYPE'] ?? $serverVars['CONTENT_TYPE'] ?? null;
        $serverVars['HTTP_CONTENT_LENGTH'] = $serverVars['HTTP_CONTENT_LENGTH'] ?? $serverVars['CONTENT_LENGTH'] ?? null;

        foreach ($serverVars as $name => $value) {
            if (false === str_starts_with($name, 'HTTP_')) {
                continue;
            }

            if (null === $value) {
                continue;
            }

            $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
            $headers[$name] = $value;
        }

        return $headers;
    }

    /**
     * Get method from PHP globals.
     *
     * @return string
     */
    protected function getMethodFromGlobals(): string
    {
        if (!empty($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            return strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        }

        if (isset($_SERVER['REQUEST_METHOD'])) {
            return strtoupper($_SERVER['REQUEST_METHOD']);
        }

        return 'GET';
    }

    /**
     * Create server request from PHP globals.
     *
     * @return ServerRequestInterface
     */
    public function createServerRequestFromGlobals(): ServerRequestInterface
    {
        // Path
        $path = null;
        if (isset($_SERVER['REQUEST_URI'])) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        }

        // Query string
        $queryString = $_SERVER['REDIRECT_QUERY_STRING'] ?? $_SERVER['QUERY_STRING'] ?? '';

        // Request URI
        $requestUri = new Uri(
            $_SERVER['REQUEST_SCHEME'] ?? '',
            $_SERVER['HTTP_HOST'] ?? '',
            (int)($_SERVER['SERVER_PORT'] ?? ($_SERVER['REQUEST_SCHEME'] == 'https' ? 443 : 80)),
            $path,
            $queryString,
            '',
            $_SERVER['PHP_AUTH_USER'] ?? '',
            $_SERVER['PHP_AUTH_PW'] ?? ''
        );

        // Server request
        return new ServerRequest(
            $this->getMethodFromGlobals(),
            $requestUri,
            $this->getHeadersFromGlobals(),
            $_COOKIE,
            $_SERVER,
            new PhpInputStream(),
            UploadedFile::parseUploadedFiles($_FILES)
        );
    }
}