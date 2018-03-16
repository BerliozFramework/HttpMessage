<?php

namespace Berlioz\Http\Message\Factory;


use Psr\Http\Message\ResponseInterface;

class ResponseFactory implements \Http\Message\ResponseFactory
{
    /**
     * Creates a new PSR-7 response.
     *
     * @param int                                                    $statusCode
     * @param string|null                                            $reasonPhrase
     * @param array                                                  $headers
     * @param resource|string|\Psr\Http\Message\StreamInterface|null $body
     * @param string                                                 $protocolVersion
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createResponse($statusCode = 200,
                                   $reasonPhrase = null,
                                   array $headers = [],
                                   $body = null,
                                   $protocolVersion = '1.1'): ResponseInterface
    {
        return (new MessageFactory)->createResponse($statusCode,
                                                    $reasonPhrase,
                                                    $headers,
                                                    $body,
                                                    $protocolVersion);
    }
}