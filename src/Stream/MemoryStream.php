<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Http\Message\Stream;

use Berlioz\Http\Message\Stream;
use RuntimeException;

/**
 * Class MemoryStream.
 *
 * @package Berlioz\Http\Message\Stream
 * @see \Berlioz\Http\Message\Stream
 */
class MemoryStream extends Stream
{
    /**
     * MemoryStream constructor.
     *
     * @throws \RuntimeException If unable to open memory stream
     */
    public function __construct()
    {
        if (false === ($fp = fopen('php://memory', 'r+'))) {
            throw new RuntimeException('Unable to open memory stream');
        }

        parent::__construct($fp);
    }
}