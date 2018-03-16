<?php

namespace Berlioz\Http\Message\Factory;


use Berlioz\Http\Message\Stream;
use Psr\Http\Message\StreamInterface;

class StreamFactory implements \Http\Message\StreamFactory
{
    /**
     * Creates a new PSR-7 stream.
     *
     * @param string|resource|StreamInterface|null $body
     *
     * @return \Psr\Http\Message\StreamInterface
     *
     * @throws \InvalidArgumentException If the stream body is invalid.
     * @throws \RuntimeException         If creating the stream from $body fails.
     */
    public function createStream($body = null): StreamInterface
    {
        if (is_null($body)) {
            return new Stream;
        } else {
            if ($body instanceof StreamInterface) {
                return $body;
            } else {
                if (is_string($body)) {
                    $stream = new Stream;
                    $stream->write($body);

                    return $stream;
                } else {
                    if (is_resource($body)) {
                        return new Stream($body);
                    } else {
                        throw new \InvalidArgumentException('Not valid stream body');
                    }
                }
            }
        }
    }
}