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

namespace Berlioz\Http\Message\Stream;

use Berlioz\Http\Message\Stream;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class MemoryStream.
 */
class MemoryStream extends Stream
{
    /**
     * MemoryStream constructor.
     *
     * @param StreamInterface|resource|string|null $contents
     *
     * @throws RuntimeException If unable to open memory stream
     */
    public function __construct(mixed $contents = null)
    {
        if (false === ($fp = fopen('php://memory', 'r+'))) {
            throw new RuntimeException('Unable to open memory stream');
        }

        parent::__construct($fp);
        $this->initStream($contents);
    }
}