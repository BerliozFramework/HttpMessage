<?php

namespace Berlioz\Http\Message\Tests;

use Berlioz\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    public function uriDataProvider()
    {
        return [// Default values
                [new Uri('http', 'www.berlioz-framework.com'),
                 ['scheme'    => 'http',
                  'host'      => 'www.berlioz-framework.com',
                  'port'      => null,
                  'path'      => '/',
                  'query'     => '',
                  'fragment'  => '',
                  'userinfo'  => '',
                  'authority' => 'www.berlioz-framework.com'],
                 'http://www.berlioz-framework.com/'],
                // Default password parameter
                [new Uri('https',
                         'www.berlioz-framework.com',
                         8080,
                         '/path/path/index.php',
                         'test=test&test2=test2',
                         'fragmentTest',
                         'elgigi'),
                 ['scheme'    => 'https',
                  'host'      => 'www.berlioz-framework.com',
                  'port'      => 8080,
                  'path'      => '/path/path/index.php',
                  'query'     => 'test=test&test2=test2',
                  'fragment'  => 'fragmentTest',
                  'userinfo'  => 'elgigi',
                  'authority' => 'elgigi@www.berlioz-framework.com:8080'],
                 'https://elgigi@www.berlioz-framework.com:8080/path/path/index.php?test=test&test2=test2#fragmentTest'],
                // Complete constructor
                [new Uri('https',
                         'www.berlioz-framework.com',
                         8080,
                         '/path/path/index.php',
                         'test=test&test2=test2',
                         'fragmentTest',
                         'elgigi',
                         'password'),
                 ['scheme'    => 'https',
                  'host'      => 'www.berlioz-framework.com',
                  'port'      => 8080,
                  'path'      => '/path/path/index.php',
                  'query'     => 'test=test&test2=test2',
                  'fragment'  => 'fragmentTest',
                  'userinfo'  => 'elgigi:password',
                  'authority' => 'elgigi:password@www.berlioz-framework.com:8080'],
                 'https://elgigi:password@www.berlioz-framework.com:8080/path/path/index.php?test=test&test2=test2#fragmentTest']];
    }

    /**
     * Test constructor and getters.
     *
     * @param \Berlioz\Http\Message\Uri $uri       Uri
     * @param array                     $uriValues Values to test
     *
     * @dataProvider uriDataProvider
     */
    public function testConstructAndGetters(Uri $uri, array $uriValues)
    {
        $this->assertEquals($uriValues['scheme'], $uri->getScheme());
        $this->assertEquals($uriValues['host'], $uri->getHost());
        $this->assertEquals($uriValues['port'], $uri->getPort());
        $this->assertEquals($uriValues['path'], $uri->getPath());
        $this->assertEquals($uriValues['fragment'], $uri->getFragment());
        $this->assertEquals($uriValues['query'], $uri->getQuery());
        $this->assertEquals($uriValues['userinfo'], $uri->getUserInfo());
        $this->assertEquals($uriValues['authority'], $uri->getAuthority());
    }

    /**
     * Test static method "createFromString".
     *
     * @param \Berlioz\Http\Message\Uri $uri       Uri
     * @param array                     $uriValues Values to test
     * @param string                    $stringUri String uri
     *
     * @dataProvider uriDataProvider
     */
    public function testCreateFromString(Uri $uri, array $uriValues, string $stringUri)
    {
        $newUri = Uri::createFromString($stringUri);

        $this->testConstructAndGetters($newUri, $uriValues);
    }

    private function getUriToTest(): Uri
    {
        return new Uri('http',
                       'www.berlioz-framework.com',
                       null,
                       '/path/path/index.php',
                       'test=test&test2=test2',
                       'fragmentTest',
                       'elgigi',
                       'password');
    }

    public function testWithScheme()
    {
        $uri = $this->getUriToTest();
        $uri2 = $uri->withScheme('https');
        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('https', $uri2->getScheme());
    }

    public function testWithUserInfo()
    {
        $uri = $this->getUriToTest();
        $uri2 = $uri->withUserInfo('newUser');
        $uri3 = $uri->withUserInfo('newUser', 'newPassword');
        $this->assertEquals('elgigi:password', $uri->getUserInfo());
        $this->assertEquals('newUser', $uri2->getUserInfo());
        $this->assertEquals('newUser:newPassword', $uri3->getUserInfo());
    }

    public function testWithHost()
    {
        $uri = $this->getUriToTest();
        $uri2 = $uri->withHost('www.berlioz-framework.net');
        $this->assertEquals('www.berlioz-framework.com', $uri->getHost());
        $this->assertEquals('www.berlioz-framework.net', $uri2->getHost());
    }

    public function testWithPort()
    {
        $uri = $this->getUriToTest();
        $uri2 = $uri->withPort(8080);
        $this->assertEquals(null, $uri->getPort());
        $this->assertEquals(8080, $uri2->getPort());
        $uri2 = $uri->withPort(443);
        $this->assertEquals(443, $uri2->getPort());
        $uri2 = $uri2->withScheme('https');
        $this->assertEquals(null, $uri2->getPort());
    }

    public function testWithPath()
    {
        $uri = $this->getUriToTest();
        $uri2 = $uri->withPath('/new-path/index.php');
        $this->assertEquals('/path/path/index.php', $uri->getPath());
        $this->assertEquals('/new-path/index.php', $uri2->getPath());
    }

    public function testWithQuery()
    {
        $uri = $this->getUriToTest();
        $uri2 = $uri->withQuery('gigi=elgigi');
        $this->assertEquals('test=test&test2=test2', $uri->getQuery());
        $this->assertEquals('gigi=elgigi', $uri2->getQuery());
        $uri2 = $uri->withQuery('');
        $this->assertEquals('', $uri2->getQuery());
    }

    public function testWithFragment()
    {
        $uri = $this->getUriToTest();
        $uri2 = $uri->withFragment('new-fragment');
        $this->assertEquals('fragmentTest', $uri->getFragment());
        $this->assertEquals('new-fragment', $uri2->getFragment());
        $uri2 = $uri->withFragment('');
        $this->assertEquals('', $uri2->getFragment());
    }

    /**
     * Test magic method "__toString".
     *
     * @param \Berlioz\Http\Message\Uri $uri       Uri
     * @param array                     $uriValues Values to test
     * @param string                    $stringUri String uri
     *
     * @dataProvider uriDataProvider
     */
    public function testToString(Uri $uri, array $uriValues, string $stringUri)
    {
        $this->assertEquals($stringUri, (string) $uri);
    }
}
