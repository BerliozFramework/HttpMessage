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

        $filter = stream_filter_append(
            $this->fp,
            filter_name: 'convert.base64-encode',
            mode: STREAM_FILTER_WRITE,
            params: $params
        );

        $this->initStream($contents);

        // Remove the write filter to force it to flush its buffered bytes (final quantum + padding).
        // Since PHP 8.4/8.5 (php-src GH-22360), the base64-encode filter only emits the trailing
        // incomplete group on close/removal, so reading without this would truncate the output.
        if (is_resource($filter)) {
            stream_filter_remove($filter);
        }
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