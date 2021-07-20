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
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class AbstractStream.
 */
abstract class AbstractStream implements StreamInterface
{
    /** @var resource Stream */
    protected $fp;

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
        $this->rewind();
        $contents = '';

        while (false === $this->eof()) {
            $contents .= $this->read(4096);
        }

        // Restore position
        $this->seek($currentPosition);

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
    public function getMetadata($key = null): mixed
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
}