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

use Berlioz\Http\Message\ServerRequest;
use Berlioz\Http\Message\Stream;
use Berlioz\Http\Message\UploadedFile;
use Berlioz\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class ServerRequestTest extends TestCase
{
    private function getServerRequest(): ServerRequest
    {
        $body = new Stream();

        return new ServerRequest(
            ServerRequest::HTTP_METHOD_POST,
            new Uri(
                'http',
                'getberlioz.com',
                null,
                '/path/path/index.php',
                'foo=bar&foo2=bar2'
            ),
            ['Content-Type' => 'application/json'],
            ['foo' => 'bar'],
            $_SERVER,
            $body,
            [],
            [
                'attr' => 'value',
                'attr2' => 'value2',
            ]
        );
    }

    public function testConstructAndGetters()
    {
        $serverRequest = $this->getServerRequest();

        $this->assertEquals($_SERVER, $serverRequest->getServerParams());
        $this->assertEquals($_SERVER['SCRIPT_NAME'], $serverRequest->getServerParam('SCRIPT_NAME'));
        $this->assertEquals('test', $serverRequest->getServerParam('FOO_BAR', 'test'));
        $this->assertEquals(['foo' => 'bar'], $serverRequest->getCookieParams());
        $this->assertEquals(['foo' => 'bar', 'foo2' => 'bar2'], $serverRequest->getQueryParams());
        $this->assertEquals('bar2', $serverRequest->getQueryParam('foo2'));
        $this->assertEquals('foo', $serverRequest->getQueryParam('bar', 'foo'));
        $this->assertEquals([], $serverRequest->getUploadedFiles());
    }

    public function testWithCookieParams()
    {
        $serverRequest = $this->getServerRequest();
        $serverRequest2 = $serverRequest->withCookieParams(['foo2' => 'bar2']);

        $this->assertNotSame($serverRequest, $serverRequest2);
        $this->assertEquals(['foo' => 'bar'], $serverRequest->getCookieParams());
        $this->assertEquals(['foo2' => 'bar2'], $serverRequest2->getCookieParams());
    }

    public function testWithAttribute()
    {
        $serverRequest = $this->getServerRequest();
        $serverRequest2 = $serverRequest->withAttribute('foo2', 'bar2');

        $this->assertNotSame($serverRequest, $serverRequest2);
        $this->assertNull($serverRequest->getAttribute('foo2'));
        $this->assertEquals('bar', $serverRequest->getAttribute('foo2', 'bar'));
        $this->assertEquals('bar2', $serverRequest2->getAttribute('foo2'));
    }

    public function testWithAttributes()
    {
        $serverRequest = $this->getServerRequest();
        $serverRequest2 = $serverRequest->withAttributes(['foo2' => 'bar2']);

        $this->assertNotSame($serverRequest, $serverRequest2);
        $this->assertEquals(
            [
                'attr' => 'value',
                'attr2' => 'value2',
            ],
            $serverRequest->getAttributes()
        );
        $this->assertEquals(['foo2' => 'bar2'], $serverRequest2->getAttributes());
    }

    public function testWithUploadedFiles()
    {
        $serverRequest = $this->getServerRequest();
        $serverRequest2 = $serverRequest->withUploadedFiles(
            [$uploadedFile1 = new UploadedFile('/foo/bar', 'foo', 'application/json', 10, UPLOAD_ERR_OK)]
        );

        $this->assertNotSame($serverRequest, $serverRequest2);
        $this->assertEquals([], $serverRequest->getUploadedFiles());
        $this->assertTrue(in_array($uploadedFile1, $serverRequest2->getUploadedFiles(), true));
    }

    public function testWithoutAttribute()
    {
        $serverRequest = $this->getServerRequest();
        $serverRequest2 = $serverRequest->withoutAttribute('attr2');

        $this->assertNotSame($serverRequest, $serverRequest2);
        $this->assertEquals(
            [
                'attr' => 'value',
                'attr2' => 'value2',
            ],
            $serverRequest->getAttributes()
        );
        $this->assertEquals(['attr' => 'value'], $serverRequest2->getAttributes());
    }

    public function testIsAjaxRequest()
    {
        $serverRequest = $this->getServerRequest();
        $this->assertFalse($serverRequest->isAjaxRequest());

        $serverRequest = $serverRequest->withHeader('AjaxRequest', 'foo');
        $this->assertTrue($serverRequest->isAjaxRequest());

        $serverRequest = new ServerRequest(
            $serverRequest->getMethod(),
            $serverRequest->getUri(),
            $serverRequest->getHeaders(),
            $serverRequest->getCookieParams(),
            array_merge($_SERVER, ['HTTP_X_REQUESTED_WITH' => 'xmlhttprequest']),
            $serverRequest->getBody()
        );
        $this->assertTrue($serverRequest->isAjaxRequest());
    }

    public function testWithQueryParams()
    {
        $serverRequest = $this->getServerRequest();
        $serverRequest2 = $serverRequest->withQueryParams(['query' => 'value']);

        $this->assertNotSame($serverRequest, $serverRequest2);
        $this->assertEquals(
            [
                'foo' => 'bar',
                'foo2' => 'bar2',
            ],
            $serverRequest->getQueryParams()
        );
        $this->assertEquals(['query' => 'value'], $serverRequest2->getQueryParams());
    }
}
