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

namespace Berlioz\Http\Message\Tests\Stream;

use Berlioz\Http\Message\Stream\GzStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class GzStreamTest extends TestCase
{
    public function testEof()
    {
        $stream = new GzStream(gzopen(__DIR__ . '/../test.txt.gz', 'r'));
        $this->assertFalse($stream->eof());
        $stream->read($stream->getSize() + 1);
        $this->assertTrue($stream->eof());
    }

    public function testRead()
    {
        $stream = new GzStream(gzopen($file = __DIR__ . '/../test.txt.gz', 'r'));

        $this->assertEquals(
            substr(gzdecode(file_get_contents($file)), 0, 5),
            $stream->read(5)
        );
    }

    public function testWrite()
    {
        $stream = new GzStream(tmpfile());

        $this->assertEquals(4, $stream->write('Test'));
        $this->assertEquals(4, $stream->getSize());
        $this->assertEquals(9, $stream->write('Test é a'));
        $this->assertEquals(13, $stream->getSize());
    }

    public function testWriteFail()
    {
        $stream = new GzStream(gzopen(__DIR__ . '/../test.txt.gz', 'r'));

        $this->expectException(RuntimeException::class);
        $stream->write('Test');
    }

    public function testGetMetadata()
    {
        $stream = new GzStream($resource = gzopen(__DIR__ . '/../test.txt.gz', 'r'));
        $this->assertEquals(stream_get_meta_data($resource), $stream->getMetadata());
    }

    public function testContents()
    {
        $stream = new GzStream(gzopen($file = __DIR__ . '/../test.txt.gz', 'r'));

        $this->assertEquals(gzdecode(file_get_contents($file)), (string)$stream);
        $this->assertEquals(gzdecode(file_get_contents($file)), $stream->getContents());
    }

    public function testIsWritable()
    {
        $stream = new GzStream(gzopen(__DIR__ . '/../test.txt.gz', 'r'));
        $this->assertFalse($stream->isWritable());

        $stream = new GzStream(gzopen(tempnam(sys_get_temp_dir(), 'berlioz'), 'w'));
        $this->assertTrue($stream->isWritable());
    }

    public function testIsReadable()
    {
        $stream = new GzStream(gzopen(tempnam(sys_get_temp_dir(), 'berlioz'), 'a'));
        $this->assertFalse($stream->isReadable());

        $stream = new GzStream(gzopen(tempnam(sys_get_temp_dir(), 'berlioz'), 'r'));
        $this->assertTrue($stream->isReadable());
    }

    public function testClose()
    {
        $stream = new GzStream(gzopen(tempnam(sys_get_temp_dir(), 'berlioz'), 'r'));
        $stream->close();
        $this->assertFalse($stream->isSeekable());
    }

    public function testDetach()
    {
        $stream = new GzStream($resource = gzopen(tempnam(sys_get_temp_dir(), 'berlioz'), 'r'));
        $detachedResource = $stream->detach();

        $this->assertFalse($stream->isSeekable());
        $this->assertSame($resource, $detachedResource);
    }

    public function testTellSeekRewind()
    {
        $stream = new GzStream($resource = gzopen(__DIR__ . '/../test.txt.gz', 'r'));

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
