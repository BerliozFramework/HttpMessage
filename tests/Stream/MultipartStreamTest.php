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

use Berlioz\Http\Message\Stream\MultipartStream;
use PHPUnit\Framework\TestCase;

class MultipartStreamTest extends TestCase
{
    public function testGetContents_empty()
    {
        $multipart = new MultipartStream();

        $this->assertEquals(
            sprintf('--%s--' . MultipartStream::EOL, $multipart->getBoundary()),
            $multipart->getContents(),
        );
    }

    public function testAddFile_detectionBase64()
    {
        $multipart = new MultipartStream();
        $multipart->addFile('my_file', __DIR__ . '/../test.txt', null);

        $this->assertEquals(
            "--{$multipart->getBoundary()}" . MultipartStream::EOL .
            "content-disposition: form-data; name=\"my_file\"; filename=\"test.txt\"" . MultipartStream::EOL .
            "content-length: 18" . MultipartStream::EOL .
            "content-type: text/plain; charset=us-ascii" . MultipartStream::EOL .
            MultipartStream::EOL .
            "It's a plain text!" . MultipartStream::EOL .
            MultipartStream::EOL .
            "--{$multipart->getBoundary()}--" . MultipartStream::EOL,
            $multipart->getContents(),
        );
    }

    public function testAddFile_withBase64()
    {
        $multipart = new MultipartStream();
        $multipart->addFile('my_file', __DIR__ . '/../test.txt', true);

        $this->assertEquals(
            "--{$multipart->getBoundary()}" . MultipartStream::EOL .
            "content-transfer-encoding: base64" . MultipartStream::EOL .
            "content-disposition: form-data; name=\"my_file\"; filename=\"test.txt\"" . MultipartStream::EOL .
            "content-length: 24" . MultipartStream::EOL .
            "content-type: text/plain; charset=us-ascii" . MultipartStream::EOL .
            MultipartStream::EOL .
            "SXQncyBhIHBsYWluIHRleHQh" . MultipartStream::EOL .
            MultipartStream::EOL .
            "--{$multipart->getBoundary()}--" . MultipartStream::EOL,
            $multipart->getContents(),
        );
    }

    public function testAddFile_withoutBase64()
    {
        $multipart = new MultipartStream();
        $multipart->addFile('my_file', __DIR__ . '/../test.txt', false);

        $this->assertEquals(
            "--{$multipart->getBoundary()}" . MultipartStream::EOL .
            "content-disposition: form-data; name=\"my_file\"; filename=\"test.txt\"" . MultipartStream::EOL .
            "content-length: 18" . MultipartStream::EOL .
            "content-type: text/plain; charset=us-ascii" . MultipartStream::EOL .
            MultipartStream::EOL .
            "It's a plain text!" . MultipartStream::EOL .
            MultipartStream::EOL .
            "--{$multipart->getBoundary()}--" . MultipartStream::EOL,
            $multipart->getContents(),
        );
    }

    public function testAddFile_binary()
    {
        $multipart = new MultipartStream();
        $multipart->addFile('my_file', __DIR__ . '/../test.txt.gz');

        $this->assertEquals(
            "--{$multipart->getBoundary()}" . MultipartStream::EOL .
            "content-transfer-encoding: base64" . MultipartStream::EOL .
            "content-disposition: form-data; name=\"my_file\"; filename=\"test.txt.gz\"" . MultipartStream::EOL .
            "content-length: 68" . MultipartStream::EOL .
            "content-type: application/gzip; charset=binary" . MultipartStream::EOL .
            MultipartStream::EOL .
            "H4sICIFNUF4EAHRlc3QudHh0AAESAO3/SXQncyBhIHBsYWluIHRleHQh1KPs+RIAAAA=" . MultipartStream::EOL .
            MultipartStream::EOL .
            "--{$multipart->getBoundary()}--" . MultipartStream::EOL,
            $multipart->getContents(),
        );
    }

    public function testAddFile_multiple()
    {
        $multipart = new MultipartStream();
        $multipart->addFile('my_first_file', __DIR__ . '/../test.txt', false);
        $multipart->addFile('my_second_file', __DIR__ . '/../test.txt.gz');

        $this->assertEquals(
            "--{$multipart->getBoundary()}" . MultipartStream::EOL .
            "content-disposition: form-data; name=\"my_first_file\"; filename=\"test.txt\"" . MultipartStream::EOL .
            "content-length: 18" . MultipartStream::EOL .
            "content-type: text/plain; charset=us-ascii" . MultipartStream::EOL .
            MultipartStream::EOL .
            "It's a plain text!" . MultipartStream::EOL .
            MultipartStream::EOL .
            "--{$multipart->getBoundary()}" . MultipartStream::EOL .
            "content-transfer-encoding: base64" . MultipartStream::EOL .
            "content-disposition: form-data; name=\"my_second_file\"; filename=\"test.txt.gz\"" . MultipartStream::EOL .
            "content-length: 68" . MultipartStream::EOL .
            "content-type: application/gzip; charset=binary" . MultipartStream::EOL .
            MultipartStream::EOL .
            "H4sICIFNUF4EAHRlc3QudHh0AAESAO3/SXQncyBhIHBsYWluIHRleHQh1KPs+RIAAAA=" . MultipartStream::EOL .
            MultipartStream::EOL .
            "--{$multipart->getBoundary()}--" . MultipartStream::EOL,
            $multipart->getContents(),
        );
    }

    public function testAddFile_withHeaders()
    {
        $multipart = new MultipartStream();
        $multipart->addFile(
            'my_file',
            __DIR__ . '/../test.txt',
            headers: [
                'foo-bar' => 'qux',
                'content-length' => 'defined'
            ]
        );

        $this->assertEquals(
            "--{$multipart->getBoundary()}" . MultipartStream::EOL .
            "foo-bar: qux" . MultipartStream::EOL .
            "content-length: defined" . MultipartStream::EOL .
            "content-disposition: form-data; name=\"my_file\"; filename=\"test.txt\"" . MultipartStream::EOL .
            "content-type: text/plain; charset=us-ascii" . MultipartStream::EOL .
            MultipartStream::EOL .
            "It's a plain text!" . MultipartStream::EOL .
            MultipartStream::EOL .
            "--{$multipart->getBoundary()}--" . MultipartStream::EOL,
            $multipart->getContents(),
        );
    }
}