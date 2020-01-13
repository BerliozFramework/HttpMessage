<?php
/**
 * Created by PhpStorm.
 * User: ronan
 * Date: 09/02/2018
 * Time: 19:14
 */

namespace Berlioz\Http\Message\Tests;

use Berlioz\Http\Message\Message;
use Berlioz\Http\Message\Stream;
use Berlioz\Http\Message\Tests\Parser\FakeParser;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    private function newMessageObj()
    {
        return new
        class extends Message {
            public function __construct()
            {
                $this->headers = [
                    'Content-Type' => ['application/json'],
                    'X-Header-Test' => ['Test', 'Test2'],
                ];
                $this->body = new Stream();
            }
        };
    }

    public function testProtocolVersion()
    {
        $message = $this->newMessageObj();
        $this->assertEquals('1.1', $message->getProtocolVersion());

        $message2 = $message->withProtocolVersion('1.0');
        $this->assertEquals('1.1', $message->getProtocolVersion());
        $this->assertEquals('1.0', $message2->getProtocolVersion());
    }

    public function testHasHeader()
    {
        $message = $this->newMessageObj();

        $this->assertEquals(true, $message->hasHeader('Content-Type'));
        $this->assertEquals(true, $message->hasHeader('contENT-type'));
        $this->assertEquals(true, $message->hasHeader('X-Header-Test'));
        $this->assertEquals(false, $message->hasHeader('Accept'));
    }

    public function testGetHeader()
    {
        $message = $this->newMessageObj();

        $this->assertEquals(['application/json'], $message->getHeader('Content-Type'));
        $this->assertEquals(['Test', 'Test2'], $message->getHeader('X-Header-Test'));
    }

    public function testGetHeaders()
    {
        $message = $this->newMessageObj();

        $this->assertEquals(
            [
                'Content-Type' => ['application/json'],
                'X-Header-Test' => ['Test', 'Test2'],
            ],
            $message->getHeaders()
        );
    }

    public function testWithHeader()
    {
        $message = $this->newMessageObj();

        $message2 = $message->withHeader('Accept', '*');
        $this->assertEquals(false, $message->hasHeader('Accept'));
        $this->assertEquals(['*'], $message2->getHeader('Accept'));
        $message2 = $message->withHeader('Content-Type', 'text/html');
        $this->assertEquals(['text/html'], $message2->getHeader('Content-Type'));
    }

    public function testWithAddedHeader()
    {
        $message = $this->newMessageObj();

        $message2 = $message->withAddedHeader('Content-Type', 'text/html');
        $this->assertEquals(['application/json', 'text/html'], $message2->getHeader('Content-Type'));
    }

    public function testWithoutHeader()
    {
        $message = $this->newMessageObj();

        $message2 = $message->withoutHeader('X-Header-Test');
        $this->assertEquals(['Content-Type' => ['application/json']], $message2->getHeaders());
    }

    public function testGetHeaderLine()
    {
        $message = $this->newMessageObj();

        $this->assertEquals('application/json', $message->getHeaderLine('Content-Type'));
        $this->assertEquals('Test, Test2', $message->getHeaderLine('X-Header-Test'));
    }

    public function testGetBody()
    {
        $message = $this->newMessageObj();

        $this->assertInstanceOf(Stream::class, $message->getBody());
    }

    public function testWithBody()
    {
        $message = $this->newMessageObj();

        $stream = $message->getBody();
        $newStream = new Stream();
        $message2 = $message->withBody($newStream);
        $this->assertEquals($stream, $message->getBody());
        $this->assertEquals($newStream, $message2->getBody());
        $this->assertNotEquals($newStream, $message->getBody());
    }

    public function testGetParsedBody()
    {
        $message = $this->newMessageObj();
        $stream = new Stream();
        $stream->write('{"json": true}');
        $message = $message->withBody($stream)
            ->withHeader('Content-Type', 'application/json');

        $this->assertObjectHasAttribute('json', $message->getParsedBody());
    }

    public function testWithParsedBody()
    {
        $message = $this->newMessageObj();
        $stream = new Stream();
        $stream->write('{"json": true}');
        $message = $message->withBody($stream)
            ->withHeader('Content-Type', 'application/json')
            ->withParsedBody(['json' => true]);

        $this->assertArrayHasKey('json', $message->getParsedBody());
    }

    public function testAddBodyParser()
    {
        $message = $this->newMessageObj();
        $message::addBodyParser(
            'application/json',
            FakeParser::class
        );
        $stream = new Stream();
        $stream->write('{"json": true}');
        $message =
            $message
                ->withBody($stream)
                ->withHeader('Content-Type', 'application/json');

        $this->assertArrayHasKey('json', $message->getParsedBody());
        $this->assertArrayHasKey('application/json', $message::getBodyParsers());
        $this->assertEquals(FakeParser::class, $message::getBodyParsers()['application/json']);
    }

    public function testAddBadParser()
    {
        $this->expectException(\InvalidArgumentException::class);

        $message = $this->newMessageObj();
        $message::addBodyParser(
            'application/json',
            \stdClass::class
        );
    }
}
