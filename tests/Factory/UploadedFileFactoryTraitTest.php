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
use Berlioz\Http\Message\UploadedFile;
use PHPUnit\Framework\TestCase;

class UploadedFileFactoryTraitTest extends TestCase
{
    private function getUploadedFilesArray(): array
    {
        return [
            "foo" =>
                [
                    "name" =>
                        [
                            "alone" => "file.txt",
                            "multiple" =>
                                [
                                    "file1.txt",
                                    "file2.txt",
                                    "file3.txt",
                                ],
                        ],
                    "type" => [
                        "alone" => "text/plain",
                        "multiple" => [
                            "text/plain",
                            "text/plain",
                            "text/plain",
                        ],
                    ],
                    "tmp_name" =>
                        [
                            "alone" => "/tmp/php24AC.tmp",
                            "multiple" =>
                                [
                                    "/tmp/php24DC.tmp",
                                    "/tmp/php24ED.tmp",
                                    "/tmp/php24FD.tmp",
                                ],
                        ],
                    "error" =>
                        [
                            "alone" => UPLOAD_ERR_OK,
                            "multiple" =>
                                [
                                    UPLOAD_ERR_OK,
                                    UPLOAD_ERR_OK,
                                    UPLOAD_ERR_OK,
                                ],
                        ],
                    "size" =>
                        [
                            "alone" => 138467,
                            "multiple" =>
                                [
                                    567916,
                                    574132,
                                    481901,
                                ],
                        ],
                ],
        ];
    }

    public function testCreateUploadedFiles()
    {
        $factory = new class {
            use UploadedFileFactoryTrait;
        };

        $parsedUploadedFiles = $factory->createUploadedFiles($this->getUploadedFilesArray());
        $this->assertCount(2, $parsedUploadedFiles['foo']);
        $this->assertInstanceOf(UploadedFile::class, $parsedUploadedFiles['foo']['alone']);
        $this->assertCount(3, $parsedUploadedFiles['foo']['multiple']);
    }

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
