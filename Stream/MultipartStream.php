<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2022 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Http\Message\Stream;

use finfo;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Stringable;
use Throwable;

class MultipartStream implements StreamInterface
{
    public const EOL = "\r\n";
    private readonly AppendStream $wrapper;
    private AppendStream $stream;

    /**
     * MultipartStream constructor.
     *
     * @param string|null $boundary
     *
     * @throws InvalidArgumentException If the boundary is not valid.
     */
    public function __construct(private ?string $boundary = null)
    {
        if (null === $this->boundary || '' === $this->boundary) {
            $this->boundary = bin2hex(random_bytes(35));
        }

        // RFC 2046, section 5.1.1: 1 to 70 bchars, not ending with a space
        if (1 !== preg_match('/\A[0-9A-Za-z\'()+_,\-.\/:=? ]{0,69}[0-9A-Za-z\'()+_,\-.\/:=?]\z/', $this->boundary)) {
            throw new InvalidArgumentException('Multipart boundary is not valid');
        }

        $this->wrapper = new AppendStream();
        $this->wrapper->addStream($this->stream = new AppendStream());
        $this->wrapper->addStream(new MemoryStream(sprintf('--%s--' . static::EOL, $this->getBoundary())));
    }

    /**
     * Get boundary.
     *
     * @return string
     */
    public function getBoundary(): string
    {
        return $this->boundary;
    }

    /**
     * Add elements.
     *
     * @param array $elements Elements, example: [fieldName => contents, ...]
     * @param array $headers
     *
     * @return void
     * @throws InvalidArgumentException If an element has invalid metadata; no element is added.
     */
    public function addElements(array $elements, array $headers = []): void
    {
        $parts = [];
        foreach ($elements as $fieldName => $contents) {
            $contents = $this->toStream($contents);
            $parts[] = [$this->buildPartHeaders((string)$fieldName, $contents, $headers), $contents];
        }

        foreach ($parts as [$partHeaders, $contents]) {
            $this->appendPart($partHeaders, $contents);
        }
    }

    /**
     * Add element.
     *
     * @param string $fieldName
     * @param StreamInterface|string|resource|null $contents
     * @param array $headers
     *
     * @return void
     * @throws InvalidArgumentException If the element has invalid metadata.
     */
    public function addElement(
        string $fieldName,
        $contents,
        array $headers = [],
    ): void {
        $this->addStream($fieldName, $this->toStream($contents), $headers);
    }

    /**
     * Add file.
     *
     * @param string $fieldName
     * @param string $filename
     * @param bool $base64
     * @param array $headers
     *
     * @return void
     * @throws InvalidArgumentException If the file part has invalid metadata.
     */
    public function addFile(
        string $fieldName,
        string $filename,
        ?bool $base64 = null,
        array $headers = [],
    ): void {
        $fileStream = new FileStream($filename);

        // Detect if base64 encoding is necessary
        if (null === $base64) {
            try {
                $finfo = new finfo(FILEINFO_MIME);
                $base64 = str_contains($finfo->file($filename), 'binary');
            } catch (Throwable) {
                $base64 = true;
            }
        }

        // Need to encode with base64
        if (true === $base64) {
            $fileStream = new Base64Stream($fileStream);
            $headers['content-transfer-encoding'] = 'base64';
        }

        $this->addStream($fieldName, $fileStream, $headers, $filename);
    }

    /**
     * Add stream.
     *
     * @param string $fieldName
     * @param StreamInterface $stream
     * @param array $headers
     * @param string|null $filename
     *
     * @return void
     * @throws InvalidArgumentException If the part has invalid metadata.
     */
    public function addStream(
        string $fieldName,
        StreamInterface $stream,
        array $headers = [],
        ?string $filename = null,
    ): void {
        $this->appendPart($this->buildPartHeaders($fieldName, $stream, $headers, $filename), $stream);
    }

    /**
     * Convert contents to stream.
     *
     * @param StreamInterface|string|resource|null $contents
     *
     * @return StreamInterface
     */
    private function toStream(mixed $contents): StreamInterface
    {
        if ($contents instanceof StreamInterface) {
            return $contents;
        }

        return new MemoryStream($contents);
    }

    /**
     * Build the delimiter and headers of a part, validating them before any bytes are appended.
     *
     * @param string $fieldName
     * @param StreamInterface $stream
     * @param array $headers
     * @param string|null $filename
     *
     * @return string
     * @throws InvalidArgumentException If the part has invalid metadata.
     */
    private function buildPartHeaders(
        string $fieldName,
        StreamInterface $stream,
        array $headers = [],
        ?string $filename = null,
    ): string {
        $headersStr = '--' . $this->getBoundary() . static::EOL;
        foreach ($this->getHeaders($fieldName, $stream, $headers, $filename) as $key => $value) {
            $headersStr .= $key . ': ' . $value . static::EOL;
        }

        return $headersStr . static::EOL;
    }

    /**
     * Append a part.
     *
     * @param string $partHeaders
     * @param StreamInterface $stream
     *
     * @return void
     */
    private function appendPart(string $partHeaders, StreamInterface $stream): void
    {
        $this->stream->addStream(new MemoryStream($partHeaders));
        $this->stream->addStream($stream);
        $this->stream->addStream(new MemoryStream(static::EOL));
    }

    /**
     * Get headers.
     *
     * @param string $fieldName
     * @param StreamInterface $stream
     * @param array $headers
     * @param string|null $filename
     *
     * @return array<string, string>
     * @throws InvalidArgumentException If a header name or value is not valid.
     */
    private function getHeaders(
        string $fieldName,
        StreamInterface $stream,
        array $headers = [],
        ?string $filename = null,
    ): array {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $name = (string)$name;

            // RFC 9110 token
            if (1 !== preg_match('/\A[!#$%&\'*+\-.^_`|~0-9A-Za-z]+\z/', $name)) {
                throw new InvalidArgumentException(sprintf('"%s" is not a valid multipart header name', $name));
            }

            $normalized[strtolower($name)] = $value;
        }
        $headers = $normalized;

        // Example of default header: Content-Disposition: form-data; name="fieldName"; filename="filename.jpg"
        if (empty($headers['content-disposition'])) {
            $headers['content-disposition'] = sprintf(
                'form-data; name="%s"',
                $this->encodeDispositionParameter($fieldName),
            );
            if (null !== $filename) {
                $headers['content-disposition'] .= sprintf(
                    '; filename="%s"',
                    $this->encodeDispositionParameter(basename($filename)),
                );
            }
        }

        // Content length
        if (!isset($headers['content-length'])) {
            $headers['content-length'] = $stream->getSize() ?? null;
        }

        // Content type
        if (!array_key_exists('content-type', $headers)) {
            try {
                $finfo = new finfo(FILEINFO_MIME);
                $contentType = null !== $filename ? $finfo->file($filename) : $finfo->buffer($stream->getContents());
            } catch (Throwable) {
                $contentType = 'application/octet-stream';
            }
            $headers['content-type'] = $contentType;
        }

        $headers = array_filter($headers, fn($value) => null !== $value);

        return array_map($this->normalizeHeaderValue(...), $headers);
    }

    /**
     * Normalize a header value.
     *
     * @param mixed $value
     *
     * @return string
     * @throws InvalidArgumentException If the value is not a string or contains control characters.
     */
    private function normalizeHeaderValue(mixed $value): string
    {
        if (!is_scalar($value) && !$value instanceof Stringable) {
            throw new InvalidArgumentException(
                sprintf('Multipart header value must be a string, "%s" given', get_debug_type($value))
            );
        }

        $value = (string)$value;

        // Control characters, except horizontal tab
        if (1 === preg_match('/[\x00-\x08\x0A-\x1F\x7F]/', $value)) {
            throw new InvalidArgumentException('Multipart header value must not contain control characters');
        }

        return $value;
    }

    /**
     * Encode a Content-Disposition parameter value (multipart/form-data, WHATWG HTML).
     *
     * Backslash is also percent-encoded, so receivers applying quoted-pair unescaping
     * (e.g. PHP) cannot read it as an escape character.
     *
     * @param string $value
     *
     * @return string
     * @throws InvalidArgumentException If the value contains a NUL character.
     */
    private function encodeDispositionParameter(string $value): string
    {
        if (str_contains($value, "\0")) {
            throw new InvalidArgumentException('Multipart field name and filename must not contain NUL characters');
        }

        return str_replace(["\r", "\n", '"', '\\'], ['%0D', '%0A', '%22', '%5C'], $value);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->wrapper->__toString();
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
        return $this->wrapper->detach();
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        return $this->wrapper->isSeekable();
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        return $this->wrapper->isWritable();
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        return $this->wrapper->isReadable();
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        return $this->wrapper->getContents();
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(?string $key = null): mixed
    {
        return $this->wrapper->getMetadata($key);
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        $this->wrapper->close();
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        return $this->wrapper->getSize();
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        return $this->wrapper->tell();
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        return $this->wrapper->eof();
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        $this->wrapper->seek($offset, $whence);
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->wrapper->rewind();
    }

    /**
     * @inheritDoc
     */
    public function write($string): int
    {
        return $this->wrapper->write($string);
    }

    /**
     * @inheritDoc
     */
    public function read($length): string
    {
        return $this->wrapper->read($length);
    }
}
