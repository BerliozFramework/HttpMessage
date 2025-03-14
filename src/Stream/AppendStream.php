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
use RuntimeException;
use Throwable;

class AppendStream implements StreamInterface
{
    /** @var StreamInterface[] */
    protected array $stream = [];
    protected bool $seekable = true;
    private int $current = 0;
    private int $position = 0;

    public function __construct(
        array $stream = [],
    ) {
        $this->addStream(...$stream);
    }

    /**
     * Add stream.
     *
     * @param StreamInterface ...$stream
     *
     * @return void
     */
    public function addStream(StreamInterface ...$stream): void
    {
        foreach ($stream as $aStream) {
            if (false === $aStream->isSeekable()) {
                $this->seekable = false;
                continue;
            }

            $aStream->rewind();
        }

        array_push($this->stream, ...$stream);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        try {
            return $this->getContents();
        } catch (Throwable) {
            return '';
        }
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
        foreach ($this->stream as $stream) {
            $stream->detach();
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        foreach ($this->stream as $stream) {
            if (false === $stream->isReadable()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        $contents = '';

        foreach ($this->stream as $stream) {
            $contents .= $stream->getContents();
        }

        return $contents;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(?string $key = null)
    {
        if (null !== $key) {
            return null;
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        foreach ($this->stream as $stream) {
            $stream->close();
        }
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        $size = 0;

        foreach ($this->stream as $stream) {
            if (null === ($streamSize = $stream->getSize())) {
                return null;
            }

            $size += $streamSize;
        }

        return $size;
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Unable to get position of stream');
        }

        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        if ($this->current === (count($this->stream) - 1)) {
            return end($this->stream)->eof();
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!$this->isSeekable()) {
            throw new RuntimeException('Unable to seek stream');
        }

        if ($whence !== SEEK_SET) {
            throw new RuntimeException('Only SEEK_SET is supported');
        }

        $this->rewind();
        $nbStreams = count($this->stream);

        for ($this->current = $this->position = 0; $this->current < $nbStreams; $this->current++) {
            if ($offset > $this->stream[$this->current]->getSize()) {
                $offset -= $this->stream[$this->current]->getSize();
                $this->position += $this->stream[$this->current]->getSize();
                continue;
            }

            $this->stream[$this->current]->seek($offset, $whence);
            $this->position += $offset;
            break;
        }
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->current = 0;
        $this->position = 0;

        foreach ($this->stream as $stream) {
            $stream->rewind();
        }
    }

    /**
     * @inheritDoc
     */
    public function write($string): int
    {
        throw new RuntimeException(static::class . ' is not writeable');
    }

    /**
     * @inheritDoc
     */
    public function read($length): string
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('Unable to read stream');
        }

        $str = '';

        do {
            $currentStream = $this->stream[$this->current];
            $char = $currentStream->read(1);

            if (false === $currentStream->eof()) {
                $str .= $char;
                $this->position++;
            }

            if ($currentStream->eof() && false === $this->eof()) {
                $this->current++;
            }
        } while (strlen($str) < $length && !$this->eof());

        $this->position = min($this->position, $this->getSize());

        return $str;
    }
}