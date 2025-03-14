<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2022 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Http\Message\Stream;

use Psr\Http\Message\StreamInterface;

class Base64Stream extends MemoryStream
{
    /**
     * Base64Stream constructor.
     *
     * @param StreamInterface|string|resource|null $contents
     */
    public function __construct(
        mixed $contents = null,
        array $params = [
            'line-length' => 76,
            'line-break-chars' => "\r\n"
        ]
    ) {
        parent::__construct();

        stream_filter_append(
            $this->fp,
            filter_name: 'convert.base64-encode',
            mode: STREAM_FILTER_WRITE,
            params: $params
        );

        $this->initStream($contents);
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        $pos = $this->tell();

        // Go to the last position and get them like size
        $this->seek(0, SEEK_END);
        $size = $this->tell();

        // Restore position
        $this->seek($pos);

        return $size;
    }
}