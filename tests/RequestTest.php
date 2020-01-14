<?php

namespace Berlioz\Http\Message\Tests;

use Berlioz\Http\Message\Request;
use Berlioz\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function requestDataProvider()
    {
        return [// Default values
                [new Request('get',
                             new Uri('http',
                                     'getberlioz.com',
                                     null,
                                     '/path/path/index.php')),
                 ['method'        => 'GET',
                  'requestTarget' => '/path/path/index.php']],
                // Complete constructor
                [new Request('post',
                             new Uri('http',
                                     'getberlioz.com',
                                     null,
                                     '/path/path/index.php'),
                             '/path/index.php'),
                 ['method'        => 'POST',
                  'requestTarget' => '/path/index.php']]];
    }

    /**
     * Test constructor and getters.
     *
     * @param \Berlioz\Http\Message\Request $request       Request
     * @param array                         $requestValues Values to test
     *
     * @dataProvider requestDataProvider
     */
    public function testConstructAndGetters(Request $request, array $requestValues)
    {
        $this->assertEquals($requestValues['requestTarget'], $request->getRequestTarget());
        $this->assertEquals($requestValues['method'], $request->getMethod());
        $this->assertInstanceOf(Uri::class, $request->getUri());
    }

    private function getRequestToTest(): Request
    {
        return new Request('get',
                           new Uri('http',
                                   'getberlioz.com',
                                   null,
                                   '/path/path/index.php',
                                   'test=test&test2=test2',
                                   'fragmentTest',
                                   'elgigi',
                                   'password'));
    }

    public function testWithRequestTarget()
    {
        $request = $this->getRequestToTest();
        $request2 = $request->withRequestTarget('/path/target.php');
        $this->assertEquals('/path/path/index.php?test=test&test2=test2', $request->getRequestTarget());
        $this->assertEquals('/path/target.php', $request2->getRequestTarget());
    }

    public function testWithMethod()
    {
        $request = $this->getRequestToTest();
        $request2 = $request->withMethod('post');
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('POST', $request2->getMethod());
    }

    public function testWithUri()
    {
        $request = $this->getRequestToTest();
        $uri = $request->getUri();
        $uri2 = new Uri('http',
                        'getberlioz.com',
                        null,
                        '/path/index.php');
        $request2 = $request->withUri($uri2);
        $this->assertNotEquals($request, $request2);
        $this->assertEquals($uri, $request->getUri());
        $this->assertEquals($uri2, $request2->getUri());
    }
}
