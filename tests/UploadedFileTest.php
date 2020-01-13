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
use Berlioz\Http\Message\UploadedFile;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UploadedFileTest extends TestCase
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

    public function testParseUploadedFiles()
    {
        $parsedUploadedFiles = UploadedFile::parseUploadedFiles($this->getUploadedFilesArray());
        $this->assertCount(2, $parsedUploadedFiles['foo']);
        $this->assertInstanceOf(UploadedFile::class, $parsedUploadedFiles['foo']['alone']);
        $this->assertCount(3, $parsedUploadedFiles['foo']['multiple']);
    }

    public function testConstructAndGetters()
    {
        $uploadedFile = new UploadedFile(
            $file = __DIR__ . '/test.txt',
            'test.txt',
            'text/plain',
            123456,
            UPLOAD_ERR_OK
        );

        $this->assertFalse($uploadedFile->hasMoved());
        $this->assertEquals(UPLOAD_ERR_OK, $uploadedFile->getError());
        $this->assertEquals('test.txt', $uploadedFile->getClientFilename());
        $this->assertEquals('text/plain', $uploadedFile->getClientMediaType());
        $this->assertEquals(123456, $uploadedFile->getSize());
        $this->assertEquals('text/plain', $uploadedFile->getMediaType());
        $this->assertEquals(sha1_file($file), $uploadedFile->getHash());
        $this->assertInstanceOf(Stream::class, $uploadedFile->getStream());
        $this->assertEquals(filesize($file), $uploadedFile->getStream()->getSize());
    }

    public function testSetStream()
    {
        $uploadedFile = new FakeUploadedFile(
            $file = __DIR__ . '/test.txt',
            'test.txt',
            'text/plain',
            123456,
            UPLOAD_ERR_OK
        );
        $stream = new Stream(fopen($file, 'r+'));
        $uploadedFile->setStream($stream);

        $this->assertSame($uploadedFile->getStream(), $stream);
    }

    public function testMoveTo()
    {
        $this->expectNotToPerformAssertions();
        $uploadedFile = new FakeUploadedFile(
            $file = __DIR__ . '/test.txt',
            'test.txt',
            'text/plain',
            123456,
            UPLOAD_ERR_OK
        );
        $uploadedFile->moveTo(tempnam(sys_get_temp_dir(), 'test'));
    }

    public function testMoveToFail()
    {
        $this->expectException(RuntimeException::class);
        $uploadedFile = new UploadedFile(
            $file = __DIR__ . '/test.txt',
            'test.txt',
            'text/plain',
            123456,
            UPLOAD_ERR_OK
        );
        $uploadedFile->moveTo(tempnam(sys_get_temp_dir(), 'test'));
    }
}
