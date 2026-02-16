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

namespace Berlioz\Http\Message\Stream;

use Exception;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Stringable;

abstract class AbstractStream implements StreamInterface, Stringable
{
    /** @var resource Stream */
    protected $fp;

    /**
     * Init stream.
     *
     * @param StreamInterface|resource|string|null $from
     *
     * @return void
     */
    protected function initStream($from): void
    {
        if (null === $from) {
            return;
        }

        if (is_resource($from)) {
            stream_copy_to_stream($from, $this->fp, offset: -1);
            return;
        }

        if ($from instanceof StreamInterface) {
            stream_copy_to_stream($from->detach(), $this->fp, offset: -1);
            return;
        }

        if (is_scalar($from)) {
            $this->write((string)$from);
            return;
        }

        throw new InvalidArgumentException(
            sprintf(
                'No supported initial type value of stream: "%s"',
                get_debug_type($from)
            )
        );
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString(): string
    {
        if (false === is_resource($this->fp)) {
            return '';
        }

        try {
            return $this->getContents();
        } catch (Exception) {
            return '';
        }
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        $fp = $this->fp;
        $this->fp = null;

        return $fp;
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable(): bool
    {
        if (null === ($seekable = $this->getMetadata('seekable'))) {
            return false;
        }

        return (bool)$seekable;
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        if (null === ($mode = $this->getMetadata('mode'))) {
            return false;
        }

        return in_array($mode, ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+']);
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        if (null === ($mode = $this->getMetadata('mode'))) {
            return false;
        }

        foreach (['r', 'r+', 'w+', 'a+', 'x+', 'c+'] as $rMode) {
            if (stripos($mode, $rMode) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws RuntimeException if unable to read or an error occurs while reading.
     */
    public function getContents(): string
    {
        $currentPosition = $this->tell();
        if ($this->isSeekable()) {
            $this->rewind();
        }
        $contents = '';

        while (false === $this->eof()) {
            $contents .= $this->read(4096);
        }

        // Restore position
        if ($this->isSeekable()) {
            $this->seek($currentPosition);
        }

        return $contents;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     *
     * @param string|null $key Specific metadata to retrieve.
     *
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata(?string $key = null): mixed
    {
        if (false === is_resource($this->fp)) {
            return null;
        }

        $metas = stream_get_meta_data($this->fp);

        if (null === $key) {
            return $metas;
        }

        if (isset($metas[$key])) {
            return $metas[$key];
        }

        return null;
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close(): void
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize(): ?int
    {
        if (!is_resource($this->fp)) {
            return null;
        }

        $stats = fstat($this->fp);

        if (!isset($stats['size'])) {
            return null;
        }

        return $stats['size'];
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws RuntimeException on error.
     */
    public function tell(): int
    {
        if (!is_resource($this->fp) || ($position = ftell($this->fp)) === false) {
            throw new RuntimeException('Unable to get position of pointer in stream');
        }

        return $position;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof(): bool
    {
        if (!is_resource($this->fp)) {
            return false;
        }

        return feof($this->fp);
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     *
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *                    based on the seek offset. Valid values are identical to the built-in
     *                    PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *                    offset bytes SEEK_CUR: Set position to current location plus offset
     *                    SEEK_END: Set position to end-of-stream plus offset.
     *
     * @throws RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!is_resource($this->fp) || fseek($this->fp, $offset, $whence) == -1) {
            throw new RuntimeException('Unable to seek stream');
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @throws RuntimeException on failure.
     * @link http://www.php.net/manual/en/function.fseek.php
     * @see  seek()
     */
    public function rewind(): void
    {
        if (!is_resource($this->fp) || rewind($this->fp) === false) {
            throw new RuntimeException('Unable to rewind stream');
        }
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     *
     * @return int Returns the number of bytes written to the stream.
     * @throws RuntimeException on failure.
     */
    public function write($string): int
    {
        $length = strlen($string);

        if (($written = @fwrite($this->fp, $string)) === false || $written !== $length) {
            throw new RuntimeException('Unable to write string to the stream');
        }

        return $written;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *                    them. Fewer than $length bytes may be returned if underlying stream
     *                    call returns fewer bytes.
     *
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws RuntimeException if an error occurs.
     */
    public function read($length): string
    {
        if (!$this->isReadable() || ($data = fread($this->fp, $length)) === false) {
            throw new RuntimeException('Unable to read stream');
        }

        return $data;
    }
}