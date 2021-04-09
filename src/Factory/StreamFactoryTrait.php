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

use Berlioz\Http\Message\Stream;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Trait StreamFactoryTrait.
 */
trait StreamFactoryTrait
{
    /**
     * Create a new stream from a string.
     *
     * The stream SHOULD be created with a temporary resource.
     *
     * @param string $content String content with which to populate the stream.
     *
     * @return StreamInterface
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
     * @return StreamInterface
     * @throws RuntimeException If the file cannot be opened.
     * @throws InvalidArgumentException If the mode is invalid.
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
     * @return StreamInterface
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}