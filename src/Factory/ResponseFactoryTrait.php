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

use Berlioz\Http\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

trait ResponseFactoryTrait
{
    use StreamFactoryTrait;

    /**
     * Create a new response.
     *
     * @param int $code The HTTP status code. Defaults to 200.
     * @param string|null $reasonPhrase The reason phrase to associate with the status code in
     *                                  the generated response. If none is provided,
     *                                  implementations MAY use the defaults as suggested in
     *                                  the HTTP specification.
     * @param array $headers
     * @param StreamInterface|resource|string|null $body
     * @param string $protocolVersion
     *
     * @return ResponseInterface
     */
    public function createResponse(
        int $code = 200,
        ?string $reasonPhrase = null,
        array $headers = [],
        mixed $body = null,
        string $protocolVersion = '1.1'
    ): ResponseInterface {
        $response = new Response($body, $code, $headers, $reasonPhrase);

        // Protocol version ?
        if ($protocolVersion != $response->getProtocolVersion()) {
            $response = $response->withProtocolVersion($protocolVersion);
        }

        return $response;
    }
}