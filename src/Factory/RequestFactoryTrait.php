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

use Berlioz\Http\Message\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Trait RequestFactoryTrait.
 */
trait RequestFactoryTrait
{
    use StreamFactoryTrait;
    use UriFactoryTrait;

    /**
     * Create a new request.
     *
     * @param string $method The HTTP method associated with the request.
     * @param UriInterface|string $uri The URI associated with the request.
     * @param array $headers
     * @param mixed $body
     * @param string $protocolVersion
     *
     * @return RequestInterface
     */
    public function createRequest(
        string $method,
        $uri,
        array $headers = [],
        mixed $body = null,
        string $protocolVersion = '1.1'
    ): RequestInterface {
        if (is_string($uri)) {
            $uri = $this->createUri($uri);
        }

        $request = new Request($method, $uri, $body, $headers);

        // Protocol version ?
        if ($protocolVersion != $request->getProtocolVersion()) {
            $request = $request->withProtocolVersion($protocolVersion);
        }

        return $request;
    }
}