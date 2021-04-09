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
 * Class PhpInputStream.
 */
class PhpInputStream extends Stream
{
    protected const FILENAME = 'php://input';
    /** @var resource PHP input stream */
    protected static $phpInputFp = null;

    /**
     * PhpInputStream constructor.
     *
     * @throws RuntimeException If unable to open memory stream
     */
    public function __construct()
    {
        if (null === static::$phpInputFp) {
            static::$phpInputFp = fopen('php://temp', 'w+');
            stream_copy_to_stream(fopen(static::FILENAME, 'r'), static::$phpInputFp);
        }

        $fp = fopen('php://temp', 'w+');
        rewind(static::$phpInputFp);
        stream_copy_to_stream(static::$phpInputFp, $fp);

        parent::__construct($fp);
    }
}