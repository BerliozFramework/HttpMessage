<?php

namespace Berlioz\Http\Message\Factory;


use Psr\Http\Message\RequestInterface;

class RequestFactory implements \Http\Message\RequestFactory
{
    /**
     * Creates a new PSR-7 request.
     *
     * @param string                                                 $method
     * @param string|\Psr\Http\Message\UriInterface                  $uri
     * @param array                                                  $headers
     * @param resource|string|\Psr\Http\Message\StreamInterface|null $body
     * @param string                                                 $protocolVersion
     *
     * @return RequestInterface
     */
    public function createRequest($method,
                                  $uri,
                                  array $headers = [],
                                  $body = null,
                                  $protocolVersion = '1.1'): RequestInterface
    {
        return (new MessageFactory)->createRequest($method, $uri, $headers, $body, $protocolVersion);
    }
}