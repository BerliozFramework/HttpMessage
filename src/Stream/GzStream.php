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

use RuntimeException;

/**
 * Class GzStream.
 */
class GzStream extends AbstractStream
{
    /**
     * Stream constructor.
     *
     * @param resource $fp
     *
     * @throws RuntimeException If parameter isn't a resource or null value
     */
    public function __construct($fp = null)
    {
        if (false === extension_loaded('zlib')) {
            throw new RuntimeException('Extension ZLIB required');
        }

        if (null !== $fp && !is_resource($fp)) {
            throw new RuntimeException('Parameter must be a resource type or null value.');
        }

        null === $fp && $fp = gzopen('php://temp', 'w');
        $this->fp = $fp;
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close(): void
    {
        if (is_resource($this->fp)) {
            gzclose($this->fp);
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

        if (false === $this->isSeekable()) {
            return null;
        }

        $currentPosition = $this->tell();
        $this->rewind();
        $size = 0;

        while (false === $this->eof()) {
            $size += strlen($this->read(4096));
        }

        // Restore position
        $this->seek($currentPosition);

        return $size;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws RuntimeException on error.
     */
    public function tell(): int
    {
        if (!is_resource($this->fp) || ($position = gztell($this->fp)) === false) {
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

        return gzeof($this->fp);
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
        if (!is_resource($this->fp) || gzseek($this->fp, $offset, $whence) == -1) {
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
        if (!is_resource($this->fp) || gzrewind($this->fp) === false) {
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

        if (($written = @gzwrite($this->fp, $string)) === false || $written !== $length) {
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
        if (!$this->isReadable() || ($data = gzread($this->fp, $length)) === false) {
            throw new RuntimeException('Unable to read stream');
        }

        return $data;
    }
}