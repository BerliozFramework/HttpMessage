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

use Berlioz\Http\Message\Uri;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * Trait UriFactoryTrait.
 */
trait UriFactoryTrait
{
    /**
     * Create a new URI.
     *
     * @param string $uri The URI to parse.
     *
     * @return UriInterface
     * @throws InvalidArgumentException If the given URI cannot be parsed.
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return Uri::createFromString($uri);
    }
}