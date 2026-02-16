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
use Psr\Http\Message\StreamInterface;
use Throwable;

class MultipartStream implements StreamInterface
{
    public const EOL = "\r\n";
    private AppendStream $wrapper;
    private AppendStream $stream;

    public function __construct(private ?string $boundary = null)
    {
        $this->boundary || $this->boundary = b_str_random(70, B_STR_RANDOM_ALPHA | B_STR_RANDOM_NUMERIC);

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
     */
    public function addElements(array $elements, array $headers = []): void
    {
        array_walk($elements, fn($value, $key) => $this->addElement($key, $value, $headers));
    }

    /**
     * Add element.
     *
     * @param string $fieldName
     * @param StreamInterface|string|resource|null $contents
     * @param array $headers
     *
     * @return void
     */
    public function addElement(
        string $fieldName,
        $contents,
        array $headers = [],
    ): void {
        if (!$contents instanceof StreamInterface) {
            $contents = new MemoryStream($contents);
        }

        $this->addStream($fieldName, $contents, $headers);
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
     */
    public function addStream(
        string $fieldName,
        StreamInterface $stream,
        array $headers = [],
        ?string $filename = null,
    ): void {
        $headersStr = '--' . $this->getBoundary() . static::EOL;
        foreach ($this->getHeaders($fieldName, $stream, $headers, $filename) as $key => $value) {
            $headersStr .= $key . ': ' . $value . static::EOL;
        }
        $headersStr .= static::EOL;

        $this->stream->addStream(new MemoryStream($headersStr));
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
     * @return array
     */
    private function getHeaders(
        string $fieldName,
        StreamInterface $stream,
        array $headers = [],
        ?string $filename = null,
    ): array {
        $keys = array_map(fn($key) => strtolower($key), array_keys($headers));
        $values = array_values($headers);
        $headers = array_combine($keys, $values);

        // Example of default header: Content-Disposition: form-data; name="fieldName"; filename="filename.jpg"
        if (empty($headers['content-disposition'])) {
            $headers['content-disposition'] = sprintf('form-data; name="%s"', $fieldName);
            if (null !== $filename) {
                $headers['content-disposition'] .= sprintf('; filename="%s"', basename($filename));
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

        return array_filter($headers, fn($value) => null !== $value);
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