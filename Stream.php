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

namespace Berlioz\Http\Message;

use Berlioz\Http\Message\Stream\AbstractStream;
use RuntimeException;

/**
 * Class Stream.
 */
class Stream extends AbstractStream
{
    /**
     * Stream constructor.
     *
     * @param resource $fp
     *
     * @throws RuntimeException If parameter isn't a resource or null value
     */
    public function __construct($fp = null)
    {
        if (null !== $fp && !is_resource($fp)) {
            throw new RuntimeException('Parameter must be a resource type or null value.');
        }

        $fp === null && $fp = fopen('php://temp', 'r+');
        $this->fp = $fp;
    }
}