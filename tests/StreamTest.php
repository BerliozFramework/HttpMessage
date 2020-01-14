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

namespace Berlioz\Http\Message\Tests;

use Berlioz\Http\Message\Stream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class StreamTest extends TestCase
{
    public function testEof()
    {
        $stream = new Stream(fopen($file = __DIR__ . '/test.txt', 'r+'));
        $this->assertFalse($stream->eof());
        $stream->read($stream->getSize() + 1);
        $this->assertTrue($stream->eof());
    }

    public function testRead()
    {
        $stream = new Stream(fopen($file = __DIR__ . '/test.txt', 'r+'));

        $this->assertEquals(
            substr(file_get_contents($file), 0, 5),
            $stream->read(5)
        );
    }

    public function testWrite()
    {
        $stream = new Stream(tmpfile());

        $this->assertEquals(4, $stream->write('Test'));
        $this->assertEquals(4, $stream->getSize());
        $this->assertEquals(9, $stream->write('Test é a'));
        $this->assertEquals(13, $stream->getSize());
    }

    public function testWriteFail()
    {
        $stream = new Stream(fopen($file = __DIR__ . '/test.txt', 'r'));

        $this->expectException(RuntimeException::class);
        $stream->write('Test');
    }

    public function testGetMetadata()
    {
        $stream = new Stream($resource = fopen($file = __DIR__ . '/test.txt', 'r'));
        $this->assertEquals(stream_get_meta_data($resource), $stream->getMetadata());
    }

    public function testContents()
    {
        $stream = new Stream(fopen($file = __DIR__ . '/test.txt', 'r+'));

        $this->assertEquals(file_get_contents($file), (string)$stream);
        $this->assertEquals(file_get_contents($file), $stream->getContents());
    }

    public function testIsWritable()
    {
        $stream = new Stream(fopen($file = __DIR__ . '/test.txt', 'r'));
        $this->assertFalse($stream->isWritable());

        $stream = new Stream(fopen($file = __DIR__ . '/test.txt', 'r+'));
        $this->assertTrue($stream->isWritable());
    }

    public function testIsReadable()
    {
        $stream = new Stream(fopen($file = __DIR__ . '/test.txt', 'a'));
        $this->assertFalse($stream->isReadable());

        $stream = new Stream(fopen($file = __DIR__ . '/test.txt', 'a+'));
        $this->assertTrue($stream->isReadable());
    }

    public function testClose()
    {
        $stream = new Stream(fopen($file = __DIR__ . '/test.txt', 'r+'));
        $stream->close();
        $this->assertFalse($stream->isSeekable());
    }

    public function testDetach()
    {
        $stream = new Stream($resource = fopen($file = __DIR__ . '/test.txt', 'r+'));
        $detachedResource = $stream->detach();

        $this->assertFalse($stream->isSeekable());
        $this->assertSame($resource, $detachedResource);
    }

    public function testTellSeekRewind()
    {
        $stream = new Stream($resource = fopen($file = __DIR__ . '/test.txt', 'r+'));

        $this->assertTrue($stream->isSeekable());
        $this->assertEquals(ftell($resource), $stream->tell());

        fseek($resource, 2);
        $this->assertEquals(2, $stream->tell());

        $stream->seek(6);
        $this->assertEquals(6, ftell($resource));
        $this->assertEquals(ftell($resource), $stream->tell());

        $stream->rewind();
        $this->assertEquals(0, ftell($resource));
    }
}
