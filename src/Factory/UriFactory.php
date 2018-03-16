<?php

namespace Berlioz\Http\Message\Factory;


use Berlioz\Http\Message\Uri;
use Psr\Http\Message\UriInterface;

class UriFactory implements \Http\Message\UriFactory
{
    /**
     * Creates an PSR-7 URI.
     *
     * @param string|\Psr\Http\Message\UriInterface $uri
     *
     * @return \Psr\Http\Message\UriInterface
     * @throws \InvalidArgumentException If the $uri argument can not be converted into a valid URI.
     */
    public function createUri($uri): UriInterface
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        } else {
            if (is_string($uri)) {
                return Uri::createFromString($uri);
            } else {
                throw new \InvalidArgumentException('Not valid URI given');
            }
        }
    }
}