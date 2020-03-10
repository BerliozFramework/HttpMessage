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

/**
 * Class PhpInputStream.
 *
 * @package Berlioz\Http\Message\Stream
 */
class PhpInputStream extends Stream
{
    protected const FILENAME = 'php://input';
    /** @var resource PHP input stream */
    protected static $phpInputFp;

    /**
     * PhpInputStream constructor.
     *
     * @throws \RuntimeException If unable to open memory stream
     */
    public function __construct()
    {
        if (null === self::$phpInputFp) {
            self::$phpInputFp = fopen('php://temp', 'w+');
            stream_copy_to_stream(fopen(static::FILENAME, 'r'), static::$phpInputFp);
        }

        $fp = fopen('php://temp', 'w+');
        rewind(self::$phpInputFp);
        stream_copy_to_stream(static::$phpInputFp, $fp);

        parent::__construct($fp);
    }
}