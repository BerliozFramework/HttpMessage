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

namespace Berlioz\Http\Message\Tests\Stream;

use Berlioz\Http\Message\Stream\AppendStream;
use Berlioz\Http\Message\Stream\MemoryStream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AppendStreamTest extends TestCase
{
    public function testAddStream()
    {
        $append = new AppendStream();

        $this->assertEquals('', $append->getContents());

        $append->addStream(new MemoryStream('Foo'), new MemoryStream('Bar'));

        $this->assertEquals('FooBar', $append->getContents());
    }

    public function testGetContents()
    {
        $append = new AppendStream([new MemoryStream('Foo'), new MemoryStream('Bar')]);

        $this->assertEquals('FooBar', $append->getContents());
    }

    public function testToString()
    {
        $append = new AppendStream([new MemoryStream('Foo'), new MemoryStream('Bar')]);

        $this->assertEquals($append->getContents(), $append->__toString());
        $this->assertEquals('FooBar', $append->__toString());
    }

    public function testDetach()
    {
        $append = new AppendStream([
            new MemoryStream('Foo'),
            new MemoryStream('Bar'),
        ]);

        $this->assertTrue($append->isReadable());
        $this->assertNull($append->detach());
        $this->assertFalse($append->isReadable());
    }

    public function testIsSeekable()
    {
        $append = new AppendStream([new MemoryStream('Foo'), new MemoryStream('Bar')]);

        $this->assertTrue($append->isSeekable());
    }

    public function testIsSeekable_false()
    {
        $stream = new MemoryStream('Foo');
        $stream->detach();
        $append = new AppendStream([$stream, new MemoryStream('Bar')]);

        $this->assertFalse($append->isSeekable());
    }

    public function testIsWritable()
    {
        $append = new AppendStream();

        $this->assertFalse($append->isWritable());
    }

    public function testIsReadable()
    {
        $append = new AppendStream([
            new MemoryStream('Foo'),
            new MemoryStream('Bar'),
        ]);

        $this->assertTrue($append->isReadable());
    }

    public function testIsReadable_false()
    {
        $stream = new MemoryStream('Foo');
        $stream->detach();
        $append = new AppendStream([$stream, new MemoryStream('Bar')]);

        $this->assertFalse($append->isReadable());
    }

    public function testGetMetaData()
    {
        $append = new AppendStream([
            new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);

        $this->assertSame([], $append->getMetadata());
        $this->assertSame(null, $append->getMetadata('FOO'));
    }

    public function testClose()
    {
        $append = new AppendStream([
            new MemoryStream('Foo'),
            new MemoryStream('Bar'),
        ]);

        $this->assertTrue($append->isReadable());
        $append->close();
        $this->assertFalse($append->isReadable());
    }

    public function testGetSize()
    {
        $append = new AppendStream([
            new MemoryStream('Foo'),
            new MemoryStream('Bar'),
        ]);

        $this->assertEquals(6, $append->getSize());
    }

    public function testGetSize_false()
    {
        $stream = new MemoryStream('Foo');
        $stream->detach();
        $append = new AppendStream([$stream, new MemoryStream('Bar')]);

        $this->assertNull($append->getSize());
    }

    public function testTell()
    {
        $append = new AppendStream([
            new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);

        $this->assertEquals(0, $append->tell());
        $this->assertEquals('Fo', $append->read(2));
        $this->assertEquals(2, $append->tell());
        $this->assertEquals('oBa', $append->read(3));
        $this->assertEquals(5, $append->tell());
        $this->assertEquals('rBazQux', $append->read(10));
        $this->assertEquals(12, $append->tell());
    }

    public function testTell_failed()
    {
        $this->expectException(RuntimeException::class);

        $append = new AppendStream([
            $stream = new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);
        $stream->detach();
        $append->tell();
    }

    public function testEof()
    {
        $append = new AppendStream([
            new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);

        $this->assertFalse($append->eof());
        $this->assertEquals('FooBarBazQux', $append->read(12));
        $this->assertFalse($append->eof());
        $append->read(1);
        $this->assertTrue($append->eof());
    }

    public function testEof_failed()
    {
        $append = new AppendStream([
            $stream = new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);
        $stream->detach();

        $this->assertFalse($append->eof());
    }

    public function testSeek_set()
    {
        $append = new AppendStream([
            new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);

        $this->assertEquals(0, $append->tell());
        $this->assertEquals('Foo', $append->read(3));
        $this->assertEquals(3, $append->tell());

        $append->seek(9, SEEK_SET);

        $this->assertEquals('Qux', $append->read(3));

        $append->seek(3, SEEK_SET);

        $this->assertEquals('Bar', $append->read(3));
    }

    public function testSeek_set_failed()
    {
        $this->expectException(RuntimeException::class);

        $append = new AppendStream([
            $stream = new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);
        $stream->detach();

        $append->seek(9, SEEK_SET);
    }

    public function testSeek_end()
    {
        $this->expectException(RuntimeException::class);

        $append = new AppendStream([
            new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);

        $append->seek(3, SEEK_END);
    }

    public function testSeek_cur()
    {
        $this->expectException(RuntimeException::class);

        $append = new AppendStream([
            new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);

        $append->seek(3, SEEK_CUR);
    }

    public function testRewind()
    {
        $append = new AppendStream([
            new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);

        $this->assertEquals(0, $append->tell());
        $this->assertEquals('FooBarBaz', $append->read(9));
        $this->assertEquals(9, $append->tell());

        $append->rewind();

        $this->assertEquals(0, $append->tell());
        $this->assertEquals('Foo', $append->read(3));
    }

    public function testRewind_failed()
    {
        $this->expectException(RuntimeException::class);

        $append = new AppendStream([
            $stream = new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);
        $stream->detach();

        $append->rewind();
    }

    public function testWrite()
    {
        $this->expectException(RuntimeException::class);

        $append = new AppendStream([
            new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);
        $append->write('test');
    }

    public function testRead()
    {
        $append = new AppendStream([
            new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);

        $this->assertEquals('Foo', $append->read(3));
        $this->assertEquals('Bar', $append->read(3));
        $this->assertEquals('Baz', $append->read(3));
        $this->assertEquals('Qux', $append->read(3));
        $this->assertEquals('', $append->read(3));
    }

    public function testRead_failed()
    {
        $this->expectException(RuntimeException::class);

        $append = new AppendStream([
            $stream = new MemoryStream('FooBar'),
            new MemoryStream('BazQux'),
        ]);
        $stream->detach();

        $append->read(3);
    }
}
