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

use Berlioz\Http\Message\Stream\GzFileStream;
use PHPUnit\Framework\TestCase;

class GzFileStreamTest extends TestCase
{
    public function test()
    {
        $stream = new GzFileStream(__DIR__ . '/../test.txt.gz');

        $this->assertEquals(
            gzdecode(file_get_contents(__DIR__ . '/../test.txt.gz')),
            $stream->getContents()
        );
    }
}
