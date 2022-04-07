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
        return new class extends Message {
            public function __construct()
            {
                parent::__construct(
                    null,
                    [
                        'Content-Type' => ['application/json'],
                        'X-Header-Test' => ['Test', 'Test2'],
                        'X-Header-Numeric' => 1234,
                    ]
                );
            }
        };
    }

    public function testProtocolVersion()
    {
        $message = $this->newMessageObj();
        $this->assertSame('1.1', $message->getProtocolVersion());

        $message2 = $message->withProtocolVersion('1.0');
        $this->assertSame('1.1', $message->getProtocolVersion());
        $this->assertSame('1.0', $message2->getProtocolVersion());
    }

    public function testHasHeader()
    {
        $message = $this->newMessageObj();

        $this->assertSame(true, $message->hasHeader('Content-Type'));
        $this->assertSame(true, $message->hasHeader('contENT-type'));
        $this->assertSame(true, $message->hasHeader('X-Header-Test'));
        $this->assertSame(false, $message->hasHeader('Accept'));
    }

    public function testGetHeader()
    {
        $message = $this->newMessageObj();

        $this->assertSame(['application/json'], $message->getHeader('Content-Type'));
        $this->assertSame(['Test', 'Test2'], $message->getHeader('X-Header-Test'));
    }

    public function testGetHeaders()
    {
        $message = $this->newMessageObj();

        $this->assertSame(
            [
                'Content-Type' => ['application/json'],
                'X-Header-Test' => ['Test', 'Test2'],
                'X-Header-Numeric' => ['1234'],
            ],
            $message->getHeaders()
        );
    }

    public function testWithHeader()
    {
        $message = $this->newMessageObj();

        $message2 = $message->withHeader('Accept', '*');

        $this->assertSame(false, $message->hasHeader('Accept'));
        $this->assertSame(['*'], $message2->getHeader('Accept'));
        $this->assertSame(
            [
                'Content-Type' => ['application/json'],
                'X-Header-Test' => ['Test', 'Test2'],
                'X-Header-Numeric' => ['1234'],
                'Accept' => ['*'],
            ],
            $message2->getHeaders()
        );

        $message2 = $message->withHeader('Content-Type', 'text/html');

        $this->assertSame(['text/html'], $message2->getHeader('Content-Type'));
        $this->assertSame(
            [
                'Content-Type' => ['text/html'],
                'X-Header-Test' => ['Test', 'Test2'],
                'X-Header-Numeric' => ['1234'],
            ],
            $message2->getHeaders()
        );
    }

    public function testWithAddedHeader()
    {
        $message = $this->newMessageObj();

        $message2 = $message
            ->withAddedHeader('Content-Type', 'text/html')
            ->withAddedHeader('X-Header-Numeric', 4321);

        $this->assertSame(['application/json', 'text/html'], $message2->getHeader('Content-Type'));
        $this->assertSame(['1234', '4321'], $message2->getHeader('X-Header-Numeric'));
        $this->assertSame(
            [
                'Content-Type' => ['application/json', 'text/html'],
                'X-Header-Test' => ['Test', 'Test2'],
                'X-Header-Numeric' => ['1234', '4321']
            ],
            $message2->getHeaders()
        );
    }

    public function testWithAddedHeader_multiple()
    {
        $message = $this->newMessageObj();

        $message2 = $message->withAddedHeader('Content-Type', ['text/xml', 'text/html', 1234]);

        $this->assertSame(
            ['application/json', 'text/xml', 'text/html', '1234'],
            $message2->getHeader('Content-Type')
        );
    }

    public function testWithoutHeader()
    {
        $message = $this->newMessageObj();

        $message2 = $message->withoutHeader('X-Header-Test');
        $this->assertSame(
            [
                'Content-Type' => ['application/json'],
                'X-Header-Numeric' => ['1234']
            ],
            $message2->getHeaders()
        );
    }

    public function testGetHeaderLine()
    {
        $message = $this->newMessageObj();

        $this->assertSame('application/json', $message->getHeaderLine('Content-Type'));
        $this->assertSame('Test, Test2', $message->getHeaderLine('X-Header-Test'));
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
        $this->assertSame($stream, $message->getBody());
        $this->assertSame($newStream, $message2->getBody());
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
        $this->assertSame(FakeParser::class, $message::getBodyParsers()['application/json']);
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

    public function testToString()
    {
        $message = $this->newMessageObj();
        $message = $message->withBody(new Stream\MemoryStream($expected = '{"foo":"bar"}'));

        $this->assertEquals($expected, (string)$message);
    }
}
