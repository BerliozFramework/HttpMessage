<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2026 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Http\Message\Validator;

use Berlioz\Http\Message\Exception\InvalidResponseException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Http1ResponseValidator.
 *
 * Validates the status line and header fields of any PSR-7 response before an HTTP/1 emission.
 * Invalid fields are rejected, never altered. The body is not read.
 *
 * "HTTP/1" refers to the produced format: a status line and "Name: value" field lines, as expected by PHP
 * header(). The server (FPM, Apache, nginx...) translates them to the protocol negotiated with the client,
 * so the protocol version of the status line is not restricted to 1.x (e.g. "2" when copied from an HTTP/2
 * request). Rules specific to HTTP/2 or HTTP/3 (lowercase names, connection-specific fields) are not checked;
 * pseudo-header fields do not exist in this format and are rejected.
 */
final class Http1ResponseValidator
{
    /**
     * Validate response.
     *
     * @param ResponseInterface $response
     *
     * @return Http1ResponseHead
     * @throws InvalidResponseException
     */
    public function validate(ResponseInterface $response): Http1ResponseHead
    {
        $protocolVersion = $response->getProtocolVersion();
        if (1 !== preg_match('/^\d(?:\.\d)?$/D', $protocolVersion)) {
            throw InvalidResponseException::invalidProtocolVersion($protocolVersion);
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode < 100 || $statusCode > 599) {
            throw InvalidResponseException::invalidStatusCode($statusCode);
        }

        $reasonPhrase = $response->getReasonPhrase();
        if (false === HttpFieldGrammar::isReasonPhrase($reasonPhrase)) {
            throw InvalidResponseException::invalidReasonPhrase();
        }

        $headers = [];
        foreach ($response->getHeaders() as $name => $values) {
            $name = (string)$name;

            if (false === HttpFieldGrammar::isFieldName($name)) {
                throw InvalidResponseException::invalidHeaderName($name);
            }

            foreach ($values as $value) {
                if (!is_string($value) || false === HttpFieldGrammar::isFieldValue($value)) {
                    throw InvalidResponseException::invalidHeaderValue($name);
                }
            }

            $headers[$name] = array_values($values);
        }

        return new Http1ResponseHead(
            statusLine: sprintf('HTTP/%s %d %s', $protocolVersion, $statusCode, $reasonPhrase),
            statusCode: $statusCode,
            headers: $headers,
        );
    }
}
