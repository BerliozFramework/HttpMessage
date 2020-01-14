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

use Exception;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class Stream.
 *
 * @package Berlioz\Http\Message
 */
class Stream implements StreamInterface
{
    /** @var resource Stream */
    protected $fp;

    /**
     * Stream constructor.
     *
     * @param resource $fp
     *
     * @throws \RuntimeException If parameter isn't a resource or null value
     */
    public function __construct($fp = null)
    {
        if (null !== $fp && !is_resource($fp)) {
            throw new RuntimeException('Parameter must be a resource type or null value.');
        }

        $this->fp = $fp;

        if (null === $fp) {
            $this->fp = fopen('php://temp', 'r+');
        }
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
    public function __toString()
    {
        if (!is_resource($this->fp)) {
            return '';
        }

        try {
            return $this->getContents();
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);
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
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
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
     * @throws \RuntimeException on error.
     */
    public function tell()
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
    public function eof()
    {
        if (!is_resource($this->fp)) {
            return false;
        }

        return feof($this->fp);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return is_resource($this->fp);
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
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
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
     * @throws \RuntimeException on failure.
     * @link http://www.php.net/manual/en/function.fseek.php
     * @see  seek()
     */
    public function rewind()
    {
        if (!is_resource($this->fp) || rewind($this->fp) === false) {
            throw new RuntimeException('Unable to rewind stream');
        }
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        if (null === ($mode = $this->getMetadata('mode'))) {
            return false;
        }

        return in_array($mode, ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+']);
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     *
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string)
    {
        $length = strlen($string);

        if (($written = @fwrite($this->fp, $string)) === false || $written !== $length) {
            throw new RuntimeException('Unable to write string to the stream');
        }

        return $written;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        if (!is_resource($this->fp) || null === ($mode = $this->getMetadata('mode'))) {
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
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *                    them. Fewer than $length bytes may be returned if underlying stream
     *                    call returns fewer bytes.
     *
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length)
    {
        if (!$this->isReadable() || ($data = fread($this->fp, $length)) === false) {
            throw new RuntimeException('Unable to read stream');
        }

        return $data;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents()
    {
        if (!$this->isReadable() || ($contents = stream_get_contents($this->fp, -1, 0)) === false) {
            throw new RuntimeException('Unable to get contents of stream');
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
     * @param string $key Specific metadata to retrieve.
     *
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        $metas = stream_get_meta_data($this->fp);

        if (null !== $key) {
            if (isset($metas[$key])) {
                return $metas[$key];
            }

            return null;
        }

        return $metas;
    }
}