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

use Berlioz\Http\Message\Parser\FormDataParser;
use Berlioz\Http\Message\Parser\FormUrlEncodedParser;
use Berlioz\Http\Message\Parser\JsonParser;
use Berlioz\Http\Message\Parser\ParserInterface;
use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Stringable;

/**
 * Class Message.
 */
abstract class Message implements MessageInterface, Stringable
{
    protected static array $bodyParser = [
        'application/json' => JsonParser::class,
        'application/x-www-form-urlencoded' => FormUrlEncodedParser::class,
        'multipart/form-data' => FormDataParser::class,
    ];
    protected StreamInterface $body;
    protected mixed $parsedBody = null;

    public function __construct(
        mixed $body,
        protected array $headers = [],
        protected ?string $protocolVersion = null,
    ) {
        $this->headers = $this->normalizeHeaders($this->headers);
        $this->body = $this->createStream($body);
    }

    /**
     * Create stream.
     *
     * @param mixed $body
     *
     * @return StreamInterface
     * @throws InvalidArgumentException
     */
    private function createStream(mixed $body): StreamInterface
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }

        if (null === $body) {
            return new Stream();
        }

        if (is_resource($body)) {
            return new Stream($body);
        }

        if (!is_scalar($body)) {
            throw new InvalidArgumentException(
                sprintf('Body must be scalar type, actual "%s" type', get_debug_type($body))
            );
        }

        $stream = new Stream();
        $stream->write((string)$body);

        return $stream;
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion ?? '1.1';
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     *
     * @return static
     */
    public function withProtocolVersion($version): static
    {
        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders(): array
    {
        return $this->headers ?? [];
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return bool Returns true if any header names match the given header
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching header name is found in the message.
     */
    public function hasHeader($name): bool
    {
        $name = ucwords(strtolower($name), ' -_');

        return isset($this->headers[$name]);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns an array of all the header values of the given
     * case-insensitive header name.
     *
     * If the header does not appear in the message, this method MUST return an
     * empty array.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string[] An array of string values as provided for the given
     *    header. If the header does not appear in the message, this method MUST
     *    return an empty array.
     */
    public function getHeader($name): array
    {
        $name = ucwords(strtolower($name), ' -_');

        return $this->headers[$name] ?? [];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($name): string
    {
        $name = ucwords(strtolower($name), ' -_');

        return isset($this->headers[$name]) ? implode(', ', $this->headers[$name]) : '';
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string $name Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     *
     * @return static
     * @throws InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value): static
    {
        return $this->withHeaders([$name => $value]);
    }

    /**
     * Return an instance with the provided value replacing the specified headers.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * @param string[] $headers Headers.
     *
     * @return static
     */
    public function withHeaders(array $headers): static
    {
        $clone = clone $this;
        $clone->headers = array_replace($clone->headers, $this->normalizeHeaders($headers));

        return $clone;
    }

    /**
     * Set headers.
     *
     * @param string[] $headers Headers.
     *
     * @return array
     */
    protected function normalizeHeaders(array $headers): array
    {
        $final = [];

        foreach ($headers as $name => $value) {
            $name = ucwords(strtolower($name), ' -_');
            $final[$name] = (array)$value;
        }

        array_walk_recursive($final, fn(&$value) => $value = (string)$value);

        return $final;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new header and/or value.
     *
     * @param string $name Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     *
     * @return static
     * @throws InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($name, $value): static
    {
        $clone = clone $this;
        $name = ucwords(strtolower($name), ' -_');
        $value = (array)$value;
        array_walk_recursive($value, fn(&$value) => $value = (string)$value);
        $clone->headers[$name] = array_merge($clone->headers[$name] ?? [], (array)$value);

        return $clone;
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $name Case-insensitive header field name to remove.
     *
     * @return static
     */
    public function withoutHeader($name): static
    {
        $clone = clone $this;
        $name = ucwords(strtolower($name), ' -_');
        unset($clone->headers[$name]);

        return $clone;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     *
     * @return static
     * @throws InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body): static
    {
        $clone = clone $this;
        $clone->body = $body;
        $clone->parsedBody = null;

        return $clone;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, this method MUST
     * return the contents of $_POST.
     *
     * Otherwise, this method may return any results of deserializing
     * the request body content; as parsing returns structured content, the
     * potential types MUST be arrays or objects only. A null value indicates
     * the absence of body content.
     *
     * @return mixed The deserialized body parameters, if any.
     *               These will typically be an array or object.
     */
    public function getParsedBody(): mixed
    {
        if ($this->parsedBody) {
            return $this->parsedBody;
        }

        $contentType = $this->getHeader('Content-Type');
        $contentType = reset($contentType);

        if (empty($contentType)) {
            return $this->parsedBody;
        }

        $contentType = explode(';', $contentType);
        $contentType = $contentType[0];
        $contentType = explode('/', $contentType, 2);
        $contentType[1] = explode('+', $contentType[1]);
        $contentType = $contentType[0] . '/' . $contentType[1][count($contentType[1]) - 1];

        $parsedBody = null;
        if (isset(static::$bodyParser[$contentType])) {
            $parsedBody = call_user_func([static::$bodyParser[$contentType], 'parseMessageBody'], $this);
        }

        if (null !== $parsedBody && !is_array($parsedBody) && !is_object($parsedBody)) {
            throw new RuntimeException('Parsed body must be an array or an object or must be null.');
        }

        return $this->parsedBody = $parsedBody;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * These MAY be injected during instantiation.
     *
     * If the request Content-Type is either application/x-www-form-urlencoded
     * or multipart/form-data, and the request method is POST, use this method
     * ONLY to inject the contents of $_POST.
     *
     * The data IS NOT REQUIRED to come from $_POST, but MUST be the results of
     * deserializing the request body content. Deserialization/parsing returns
     * structured data, and, as such, this method ONLY accepts arrays or objects,
     * or a null value if nothing was available to parse.
     *
     * As an example, if content negotiation determines that the request data
     * is a JSON payload, this method could be used to create a request
     * instance with the deserialized parameters.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated body parameters.
     *
     * @param object|array|null $data The deserialized body data. This will
     *                                typically be in an array or object.
     *
     * @return ServerRequestInterface|static
     */
    public function withParsedBody($data): ServerRequestInterface|static
    {
        $clone = clone $this;
        $clone->parsedBody = $data;

        return $clone;
    }

    /**
     * Get body parsers.
     *
     * @return string[]
     */
    public static function getBodyParsers(): array
    {
        return static::$bodyParser;
    }

    /**
     * Add body parser.
     *
     * @param array|string $mime Body parser
     * @param string $parserClass Parser class
     *
     * @throws InvalidArgumentException
     */
    public static function addBodyParser(array|string $mime, string $parserClass): void
    {
        if (!is_a($parserClass, ParserInterface::class, true)) {
            throw new InvalidArgumentException(
                sprintf('Parser class must implements %s interface', ParserInterface::class)
            );
        }

        foreach ((array)$mime as $aMime) {
            static::$bodyParser[$aMime] = $parserClass;
        }
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return (string)$this->getBody();
    }
}