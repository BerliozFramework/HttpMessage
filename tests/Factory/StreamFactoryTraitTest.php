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

namespace Berlioz\Http\Message\Tests\Factory;

use Berlioz\Http\Message\Factory\StreamFactoryTrait;
use PHPUnit\Framework\TestCase;

class StreamFactoryTraitTest extends TestCase
{
    public function testCreateStream()
    {
        $factory = new class {
            use StreamFactoryTrait;
        };
        $stream = $factory->createStream('Test');

        $this->assertEquals('Test', $stream->getContents());
    }

    public function testCreateStreamFromResource()
    {
        $factory = new class {
            use StreamFactoryTrait;
        };
        $stream = $factory->createStreamFromResource($resource = tmpfile());

        $this->assertSame($resource, $stream->detach());
    }

    public function testCreateStreamFromFile()
    {
        $factory = new class {
            use StreamFactoryTrait;
        };
        $stream = $factory->createStreamFromFile($file = __DIR__ . '/../test.txt');

        $this->assertEquals(file_get_contents($file), $stream->getContents());
    }
}
