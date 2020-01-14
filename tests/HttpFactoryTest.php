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

use Berlioz\Http\Message\HttpFactory;
use Berlioz\Http\Message\Request;
use Berlioz\Http\Message\Response;
use Berlioz\Http\Message\Stream\MemoryStream;
use Berlioz\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class HttpFactoryTest extends TestCase
{
    public function testCreateUploadedFile()
    {
        $factory = new HttpFactory();
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

    public function testCreateStream()
    {
        $factory = new HttpFactory();
        $stream = $factory->createStream('Test');

        $this->assertEquals('Test', $stream->getContents());
    }

    public function testCreateStreamFromResource()
    {
        $factory = new HttpFactory();
        $stream = $factory->createStreamFromResource($resource = tmpfile());

        $this->assertSame($resource, $stream->detach());
    }

    public function testCreateStreamFromFile()
    {
        $factory = new HttpFactory();
        $stream = $factory->createStreamFromFile($file = __DIR__ . '/test.txt');

        $this->assertEquals(file_get_contents($file), $stream->getContents());
    }

    public function testCreateServerRequest()
    {
        $factory = new HttpFactory();
        $serverRequest = $factory->createServerRequest(
            Request::HTTP_METHOD_POST,
            $uriTxt = 'https://getberlioz.com/test',
            $_SERVER
        );

        $this->assertInstanceOf(Uri::class, $serverRequest->getUri());
        $this->assertEquals($uriTxt, (string)$serverRequest->getUri());
        $this->assertEquals(Request::HTTP_METHOD_POST, $serverRequest->getMethod());
        $this->assertEquals($_SERVER, $serverRequest->getServerParams());
    }

    public function testCreateRequest()
    {
        $factory = new HttpFactory();
        $request = $factory->createRequest(
            Request::HTTP_METHOD_POST,
            $uriTxt = 'https://getberlioz.com/test',
            [
                'Content-Type' => 'application/json',
            ],
            $stream = new MemoryStream(),
            '1.0'
        );

        $this->assertInstanceOf(Uri::class, $request->getUri());
        $this->assertEquals($uriTxt, (string)$request->getUri());
        $this->assertEquals(Request::HTTP_METHOD_POST, $request->getMethod());
        $this->assertEquals(['Content-Type' => ['application/json']], $request->getHeaders());
        $this->assertSame($stream, $request->getBody());
        $this->assertEquals('1.0', $request->getProtocolVersion());
    }

    public function testCreateResponse()
    {
        $factory = new HttpFactory();
        $response = $factory->createResponse(
            Response::HTTP_STATUS_NOT_FOUND,
            'Not found resource',
            [
                'Content-Type' => 'application/json',
            ],
            $stream = new MemoryStream(),
            '1.0'
        );

        $this->assertEquals(Response::HTTP_STATUS_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals('Not found resource', $response->getReasonPhrase());
        $this->assertEquals(['Content-Type' => ['application/json']], $response->getHeaders());
        $this->assertSame($stream, $response->getBody());
        $this->assertEquals('1.0', $response->getProtocolVersion());
    }

    public function testCreateUri()
    {
        $factory = new HttpFactory();
        $uri = $factory->createUri('https://getberlioz.com/foo/bar?test=value#hash');

        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('getberlioz.com', $uri->getHost());
        $this->assertEquals('/foo/bar', $uri->getPath());
        $this->assertEquals('test=value', $uri->getQuery());
        $this->assertEquals('hash', $uri->getFragment());
    }
}
