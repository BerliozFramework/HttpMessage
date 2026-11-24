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

namespace Berlioz\Http\Message\Exception;

use InvalidArgumentException;

/**
 * Class InvalidResponseException.
 *
 * Thrown when a response cannot be emitted as a valid HTTP/1 message.
 */
class InvalidResponseException extends InvalidArgumentException
{
    public static function invalidProtocolVersion(string $version): self
    {
        return new self(sprintf('"%s" is not a valid HTTP protocol version', addcslashes($version, "\0..\37\177")));
    }

    public static function invalidStatusCode(int $statusCode): self
    {
        return new self(sprintf('"%d" is not a valid HTTP status code', $statusCode));
    }

    public static function invalidReasonPhrase(): self
    {
        return new self('Reason phrase contains forbidden characters');
    }

    public static function invalidHeaderName(string $name): self
    {
        return new self(sprintf('"%s" is not a valid HTTP/1 header name', addcslashes($name, "\0..\37\177")));
    }

    public static function invalidHeaderValue(string $name): self
    {
        return new self(sprintf('Value of header "%s" is not a valid HTTP field value', $name));
    }
}
