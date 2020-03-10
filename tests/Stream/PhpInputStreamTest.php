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

namespace Berlioz\Http\Message\Tests\Stream;

use PHPUnit\Framework\TestCase;

class PhpInputStreamTest extends TestCase
{
    public function testConstruct()
    {
        $stream = new FakePhpInputStream();

        $this->assertInstanceOf(FakePhpInputStream::class, $stream);
    }

    public function testStream()
    {
        $stream = new FakePhpInputStream();

        $this->assertEquals(file_get_contents(__DIR__ . '/phpinput.txt'), $stream->getContents());

        rewind($stream->getFakeStream());
        $this->assertEquals(fread($stream->getFakeStream(), 1024), $stream->getContents());

        // Test new instance
        $stream2 = new FakePhpInputStream();
        $this->assertGreaterThan(0, $stream->getSize());
        $this->assertEquals($stream->getContents(), $stream2->getContents());
    }
}
