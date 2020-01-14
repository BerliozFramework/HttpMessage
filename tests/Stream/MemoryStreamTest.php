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

use Berlioz\Http\Message\Stream\MemoryStream;
use PHPUnit\Framework\TestCase;

class MemoryStreamTest extends TestCase
{
    public function testConstruct()
    {
        $stream = new MemoryStream();

        $this->assertInstanceOf(MemoryStream::class, $stream);
    }
}
