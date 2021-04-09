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

use Berlioz\Http\Message\Factory\UploadedFileFactoryTrait;
use Berlioz\Http\Message\Stream\MemoryStream;
use PHPUnit\Framework\TestCase;

class UploadedFileFactoryTraitTest extends TestCase
{
    public function testCreateUploadedFile()
    {
        $factory = new class {
            use UploadedFileFactoryTrait;
        };
        $uploadedFile =
            $factory->createUploadedFile(
                $stream = new MemoryStream(),
                123456,
                UPLOAD_ERR_OK,
                'foo.txt',
                'text/plain',
                '/tmp/tempfile'
            );

        $this->assertSame($stream, $uploadedFile->getStream());
        $this->assertEquals(123456, $uploadedFile->getSize());
        $this->assertEquals(UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertEquals('foo.txt', $uploadedFile->getClientFilename());
        $this->assertEquals('text/plain', $uploadedFile->getClientMediaType());
    }
}
