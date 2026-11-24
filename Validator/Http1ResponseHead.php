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

/**
 * Class Http1ResponseHead.
 *
 * Validated status line and header fields of a response, ready for HTTP/1 emission.
 */
final readonly class Http1ResponseHead
{
    /**
     * @param string $statusLine Status line, e.g. "HTTP/1.1 200 OK"
     * @param int $statusCode Status code
     * @param array<string, list<string>> $headers Header values by name, in response order
     */
    public function __construct(
        public string $statusLine,
        public int $statusCode,
        public array $headers,
    ) {
    }
}
