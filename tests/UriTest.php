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

namespace Berlioz\Http\Message\Tests;

use Berlioz\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    public function uriDataProvider()
    {
        return [
            // Empty
            [
                new Uri('', '', null, ''),
                [
                    'scheme' => '',
                    'host' => '',
                    'port' => null,
                    'path' => '',
                    'query' => '',
                    'fragment' => '',
                    'userinfo' => '',
                    'authority' => ''
                ],
                ''
            ],
            // Only path
            [
                new Uri('', '', null, 'index.php'),
                [
                    'scheme' => '',
                    'host' => '',
                    'port' => null,
                    'path' => 'index.php',
                    'query' => '',
                    'fragment' => '',
                    'userinfo' => '',
                    'authority' => ''
                ],
                'index.php'
            ],
            // With path
            [
                new Uri('https', 'getberlioz.com', null, '/index.php'),
                [
                    'scheme' => 'https',
                    'host' => 'getberlioz.com',
                    'port' => null,
                    'path' => '/index.php',
                    'query' => '',
                    'fragment' => '',
                    'userinfo' => '',
                    'authority' => 'getberlioz.com'
                ],
                'https://getberlioz.com/index.php'
            ],
            // Without path
            [
                new Uri('http', 'getberlioz.com'),
                [
                    'scheme' => 'http',
                    'host' => 'getberlioz.com',
                    'port' => null,
                    'path' => '',
                    'query' => '',
                    'fragment' => '',
                    'userinfo' => '',
                    'authority' => 'getberlioz.com'
                ],
                'http://getberlioz.com'
            ],
            // Default password parameter
            [
                new Uri(
                    'https',
                    'getberlioz.com',
                    8080,
                    '/path/path/index.php',
                    'test=test&test2=test2',
                    'fragmentTest',
                    'elgigi'
                ),
                [
                    'scheme' => 'https',
                    'host' => 'getberlioz.com',
                    'port' => 8080,
                    'path' => '/path/path/index.php',
                    'query' => 'test=test&test2=test2',
                    'fragment' => 'fragmentTest',
                    'userinfo' => 'elgigi',
                    'authority' => 'elgigi@getberlioz.com:8080'
                ],
                'https://elgigi@getberlioz.com:8080/path/path/index.php?test=test&test2=test2#fragmentTest'
            ],
            // Fragment '0'
            [
                new Uri(
                    'https',
                    'getberlioz.com',
                    8080,
                    '/',
                    '',
                    '0'
                ),
                [
                    'scheme' => 'https',
                    'host' => 'getberlioz.com',
                    'port' => 8080,
                    'path' => '/',
                    'query' => '',
                    'fragment' => '0',
                    'userinfo' => '',
                    'authority' => 'getberlioz.com:8080'
                ],
                'https://getberlioz.com:8080/#0'
            ],
            // User info encoded
            [
                new Uri(
                    'https',
                    'getberlioz.com',
                    user: 'el gigi',
                    password: 'pass word'
                ),
                [
                    'scheme' => 'https',
                    'host' => 'getberlioz.com',
                    'port' => null,
                    'path' => '',
                    'query' => '',
                    'fragment' => '',
                    'userinfo' => 'el%20gigi:pass%20word',
                    'authority' => 'el%20gigi:pass%20word@getberlioz.com'
                ],
                'https://el%20gigi:pass%20word@getberlioz.com'
            ],
            // Complete constructor
            [
                new Uri(
                    'https',
                    'getberlioz.com',
                    8080,
                    '/path/path/index.php',
                    'test=test&test2=test2',
                    'fragmentTest',
                    'elgigi',
                    'password'
                ),
                [
                    'scheme' => 'https',
                    'host' => 'getberlioz.com',
                    'port' => 8080,
                    'path' => '/path/path/index.php',
                    'query' => 'test=test&test2=test2',
                    'fragment' => 'fragmentTest',
                    'userinfo' => 'elgigi:password',
                    'authority' => 'elgigi:password@getberlioz.com:8080'
                ],
                'https://elgigi:password@getberlioz.com:8080/path/path/index.php?test=test&test2=test2#fragmentTest'
            ]
        ];
    }

    /**
     * Test constructor and getters.
     *
     * @param Uri $uri Uri
     * @param array $uriValues Values to test
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
     * @dataProvider uriDataProvider
     */
    public function testJsonSerialize(Uri $uri, array $uriValues, string $expected)
    {
        $this->assertEquals(json_encode($expected), json_encode($uri));
    }

    /**
     * Test static method "createFromString".
     *
     * @param Uri $uri Uri
     * @param array $uriValues Values to test
     * @param string $stringUri String uri
     *
     * @dataProvider uriDataProvider
     */
    public function testCreateFromString(Uri $uri, array $uriValues, string $stringUri)
    {
        $newUri = Uri::createFromString($stringUri);

        $this->testConstructAndGetters($newUri, $uriValues);
    }

    public function testCreateFromString_withBlankSpaces()
    {
        $uri = Uri::createFromString(' https://getberlioz.com/doc ');
        $this->assertEquals('https://getberlioz.com/doc', $uri);
    }

    public function testCreate()
    {
        $uri = Uri::createFromString('../qux?foo&bar=bar value#bar');
        $ref = Uri::createFromString('https://elgigi:password@getberlioz.com:8080/doc/#qux');

        $newUri = Uri::create($uri, $ref);

        $this->testConstructAndGetters(
            $newUri,
            [
                'scheme' => 'https',
                'host' => 'getberlioz.com',
                'port' => 8080,
                'path' => '/qux',
                'query' => 'foo&bar=bar value',
                'fragment' => 'bar',
                'userinfo' => 'elgigi:password',
                'authority' => 'elgigi:password@getberlioz.com:8080'
            ]
        );
        $this->assertEquals(
            'https://elgigi:password@getberlioz.com:8080/qux?foo&bar=bar+value#bar',
            (string)$newUri
        );
    }

    public function testCreate_withAbsoluteLink()
    {
        $uri = Uri::createFromString('/foo/bar/baz/qux.html');
        $ref = Uri::createFromString('https://getberlioz.com/foo/bar/');

        $newUri = Uri::create($uri, $ref);

        $this->testConstructAndGetters(
            $newUri,
            [
                'scheme' => 'https',
                'host' => 'getberlioz.com',
                'port' => null,
                'path' => '/foo/bar/baz/qux.html',
                'query' => '',
                'fragment' => '',
                'userinfo' => '',
                'authority' => 'getberlioz.com'
            ]
        );
    }

    public function testCreateEmpty()
    {
        $uri = Uri::createFromString('');
        $ref = Uri::createFromString('https://elgigi:password@getberlioz.com:8080/doc/#qux');

        $newUri = Uri::create($uri, $ref);

        $this->testConstructAndGetters(
            $newUri,
            [
                'scheme' => 'https',
                'host' => 'getberlioz.com',
                'port' => 8080,
                'path' => '/doc/',
                'query' => '',
                'fragment' => '',
                'userinfo' => 'elgigi:password',
                'authority' => 'elgigi:password@getberlioz.com:8080'
            ]
        );
    }

    public function testCreateEmpty2()
    {
        $uri = Uri::createFromString('');
        $ref = Uri::createFromString('https://elgigi:password@getberlioz.com:8080/doc/index.php#qux');

        $newUri = Uri::create($uri, $ref);

        $this->testConstructAndGetters(
            $newUri,
            [
                'scheme' => 'https',
                'host' => 'getberlioz.com',
                'port' => 8080,
                'path' => '/doc/index.php',
                'query' => '',
                'fragment' => '',
                'userinfo' => 'elgigi:password',
                'authority' => 'elgigi:password@getberlioz.com:8080'
            ]
        );
    }

    public function testCreate_withoutSchema()
    {
        $uri = Uri::createFromString('//getberlioz.com:8080/doc');
        $ref = Uri::createFromString($refUri = 'https://elgigi:password@gethectororm.com/doc/#qux');

        $expected = [
            'scheme' => 'https',
            'host' => 'getberlioz.com',
            'port' => 8080,
            'path' => '/doc',
            'query' => '',
            'fragment' => '',
            'userinfo' => '',
            'authority' => 'getberlioz.com:8080'
        ];

        $newUri = Uri::create($uri, $ref);

        $this->testConstructAndGetters($newUri, $expected);

        $newUri = Uri::create($uri, $refUri);

        $this->testConstructAndGetters($newUri, $expected);
    }

    public function testCreate_withBlankSpaces()
    {
        $uri = Uri::create(' https://getberlioz.com/doc ');
        $this->assertEquals('https://getberlioz.com/doc', $uri);
    }

    private function getUriToTest(): Uri
    {
        return new Uri(
            'http',
            'getberlioz.com',
            null,
            '/path/path/index.php',
            'foo=bar&baz=qux',
            'fragmentTest',
            'elgigi',
            'password'
        );
    }

    public function testGetQueryValue()
    {
        $uri = $this->getUriToTest();

        $this->assertEquals('bar', $uri->getQueryValue('foo'));
        $this->assertNull($uri->getQueryValue('bar'));
        $this->assertEquals('default', $uri->getQueryValue('bar', 'default'));
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
        $uri2 = $uri->withHost('getberlioz.net');
        $this->assertEquals('getberlioz.com', $uri->getHost());
        $this->assertEquals('getberlioz.net', $uri2->getHost());
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
        $this->assertEquals('foo=bar&baz=qux', $uri->getQuery());
        $this->assertEquals('gigi=elgigi', $uri2->getQuery());
        $uri2 = $uri->withQuery('');
        $this->assertEquals('', $uri2->getQuery());
    }

    public function testWithAddedQuery()
    {
        $uri = $this->getUriToTest();
        $uri2 = $uri->withAddedQuery('gigi=elgigi');

        $this->assertEquals('foo=bar&baz=qux', $uri->getQuery());
        $this->assertEquals('foo=bar&baz=qux&gigi=elgigi', $uri2->getQuery());

        $uri3 = $uri2->withAddedQuery('gigi[]=elgigi2&gigi[]=elgigi3');

        $this->assertEquals(
            'foo=bar&baz=qux&gigi%5B0%5D=elgigi&gigi%5B1%5D=elgigi2&gigi%5B2%5D=elgigi3',
            $uri3->getQuery()
        );
    }

    public function testWithoutQuery()
    {
        $uri = $this->getUriToTest();
        $uri2 = $uri->withoutQuery('baz');

        $this->assertEquals('foo=bar&baz=qux', $uri->getQuery());
        $this->assertEquals('foo=bar', $uri2->getQuery());
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
     * @param Uri $uri Uri
     * @param array $uriValues Values to test
     * @param string $stringUri String uri
     *
     * @dataProvider uriDataProvider
     */
    public function testToString(Uri $uri, array $uriValues, string $stringUri)
    {
        $this->assertEquals($stringUri, (string)$uri);
    }
}
