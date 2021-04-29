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
use RuntimeException;

/**
 * Class FileStream.
 */
class FileStream extends Stream
{
    /**
     * FileStream constructor.
     *
     * @param string $filename
     * @param string $mode
     *
     * @throws RuntimeException If unable to open file
     */
    public function __construct(string $filename, string $mode = 'r+')
    {
        if (false === ($fp = fopen($filename, $mode))) {
            throw new RuntimeException(sprintf('Unable to open file "%s" (with mode "%s")', $filename, $mode));
        }

        parent::__construct($fp);
    }
}