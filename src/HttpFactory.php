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

use InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

use const UPLOAD_ERR_OK;

/**
 * Class HttpFactory.
 *
 * @package Berlioz\Http\Message
 */
class HttpFactory implements
    RequestFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface,
    StreamFactoryInterface,
    UploadedFileFactoryInterface,
    UriFactoryInterface
{
    ///////////////////////////////
    /// RequestFactoryInterface ///
    ///////////////////////////////

    /**
     * Create a new request.
     *
     * @param string $method The HTTP method associated with the request.
     * @param UriInterface|string $uri The URI associated with the request.
     * @param array $headers
     * @param resource|string|StreamInterface|null $body
     * @param string $protocolVersion
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function createRequest(
        string $method,
        $uri,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ): RequestInterface {
        if (is_string($uri)) {
            $uri = $this->createUri($uri);
        }

        $request = new Request($method, $uri);

        // Headers ?
        if (!empty($headers)) {
            $request = $request->withHeaders($headers);
        }

        // Body ?
        if (null !== $body) {
            if ($body instanceof StreamInterface) {
                $request = $request->withBody($body);
            }

            if (is_resource($body)) {
                $request = $request->withBody($this->createStreamFromResource($body));
            }

            if (is_string($body)) {
                $request = $request->withBody($this->createStream($body));
            }
        }

        // Protocol version ?
        if ($protocolVersion != $request->getProtocolVersion()) {
            $request = $request->withProtocolVersion($protocolVersion);
        }

        return $request;
    }

    ////////////////////////////////
    /// ResponseFactoryInterface ///
    ////////////////////////////////

    /**
     * Create a new response.
     *
     * @param int $code The HTTP status code. Defaults to 200.
     * @param string $reasonPhrase The reason phrase to associate with the status code in
     *                                                           the generated response. If none is provided,
     *                                                           implementations MAY use the defaults as suggested in
     *                                                           the HTTP specification.
     * @param array $headers
     * @param resource|string|StreamInterface|null $body
     * @param string $protocolVersion
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createResponse(
        int $code = 200,
        string $reasonPhrase = null,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    ): ResponseInterface {
        $stream = null;
        if (null !== $body) {
            if ($body instanceof StreamInterface) {
                $stream = $body;
            }

            if (is_resource($body)) {
                $stream = $this->createStreamFromResource($body);
            }

            if (is_string($body)) {
                $stream = $this->createStream($body);
            }
        }

        $response =
            new Response(
                $stream,
                $code,
                $headers,
                $reasonPhrase
            );

        // Protocol version ?
        if ($protocolVersion != $response->getProtocolVersion()) {
            $response = $response->withProtocolVersion($protocolVersion);
        }

        return $response;
    }

    /////////////////////////////////////
    /// ServerRequestFactoryInterface ///
    /////////////////////////////////////

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
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if (is_string($uri)) {
            $uri = $this->createUri($uri);
        }

        return new ServerRequest($method, $uri, [], [], $serverParams, new Stream());
    }

    //////////////////////////////
    /// StreamFactoryInterface ///
    //////////////////////////////

    /**
     * Create a new stream from a string.
     *
     * The stream SHOULD be created with a temporary resource.
     *
     * @param string $content String content with which to populate the stream.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $stream = new Stream();
        $stream->write($content);

        return $stream;
    }

    /**
     * Create a stream from an existing file.
     *
     * The file MUST be opened using the given mode, which may be any mode
     * supported by the `fopen` function.
     *
     * The `$filename` MAY be any string supported by `fopen()`.
     *
     * @param string $filename The filename or stream URI to use as basis of stream.
     * @param string $mode The mode with which to open the underlying filename/stream.
     *
     * @return \Psr\Http\Message\StreamInterface
     * @throws \RuntimeException If the file cannot be opened.
     * @throws \InvalidArgumentException If the mode is invalid.
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        if (empty($filename)) {
            throw new RuntimeException('Filename cannot be empty');
        }

        if (($resource = @fopen($filename, $mode)) === false) {
            throw new RuntimeException(sprintf('Unable to open file "%s" with mode "%s"', $filename, $mode));
        }

        return new Stream($resource);
    }

    /**
     * Create a new stream from an existing resource.
     *
     * The stream MUST be readable and may be writable.
     *
     * @param resource $resource The PHP resource to use as the basis for the stream.
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }

    ////////////////////////////////////
    /// UploadedFileFactoryInterface ///
    ////////////////////////////////////

    /**
     * Create a new uploaded file.
     *
     * If a size is not provided it will be determined by checking the size of
     * the stream.
     *
     * @link http://php.net/manual/features.file-upload.post-method.php
     * @link http://php.net/manual/features.file-upload.errors.php
     *
     * @param StreamInterface $stream The underlying stream representing the
     *                                         uploaded file content.
     * @param int $size The size of the file in bytes.
     * @param int $error The PHP file upload error.
     * @param string $clientFilename The filename as provided by the client, if any.
     * @param string $clientMediaType The media type as provided by the client, if any.
     * @param string $filename Filename
     *
     * @return \Psr\Http\Message\UploadedFileInterface
     * @throws \InvalidArgumentException If the file resource is not readable.
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null,
        string $filename = ''
    ): UploadedFileInterface {
        if (null === $size) {
            $size = $stream->getSize();
        }

        $uploadedFile = new UploadedFile($filename, $clientFilename, $clientMediaType, $size, $error);
        $uploadedFile->setStream($stream);

        return $uploadedFile;
    }

    ///////////////////////////
    /// UriFactoryInterface ///
    ///////////////////////////

    /**
     * Create a new URI.
     *
     * @param string $uri The URI to parse.
     *
     * @return \Psr\Http\Message\UriInterface
     * @throws \InvalidArgumentException If the given URI cannot be parsed.
     */
    public function createUri(string $uri = ''): UriInterface
    {
        if (!is_string($uri)) {
            throw new InvalidArgumentException('Not valid URI given');
        }

        return Uri::createFromString($uri);
    }
}