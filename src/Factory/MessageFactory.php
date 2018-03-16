<?php

namespace Berlioz\Http\Message\Factory;


use Berlioz\Http\Message\Request;
use Berlioz\Http\Message\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MessageFactory implements \Http\Message\MessageFactory
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
     * @return \Psr\Http\Message\RequestInterface
     */
    public function createRequest($method,
                                  $uri,
                                  array $headers = [],
                                  $body = null,
                                  $protocolVersion = '1.1'): RequestInterface
    {
        $request = new Request($method, (new UriFactory)->createUri($uri));

        // Headers ?
        if (empty($headers)) {
            $request = $request->withHeaders($headers);
        }

        // Body ?
        if (!is_null($body)) {
            $request = $request->withBody((new StreamFactory)->createStream($body));
        }

        // Protocol version ?
        if ($protocolVersion != $request->getProtocolVersion()) {
            $request = $request->withProtocolVersion($protocolVersion);
        }

        return $request;
    }

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
        $response = new Response((new StreamFactory)->createStream($body),
                                 $statusCode,
                                 $headers,
                                 $reasonPhrase);

        // Protocol version ?
        if ($protocolVersion != $response->getProtocolVersion()) {
            $response = $response->withProtocolVersion($protocolVersion);
        }

        return $response;
    }
}