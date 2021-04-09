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

use Berlioz\Http\Message\Factory\ServerRequestFactoryTrait;
use Berlioz\Http\Message\Request;
use Berlioz\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class ServerRequestFactoryTraitTest extends TestCase
{
    public function testCreateServerRequest()
    {
        $factory = new class {
            use ServerRequestFactoryTrait;
        };
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

    public function testCreateServerRequestFromGlobals()
    {
        $uri = 'https://getberlioz.com/test?foo=bar&baz=qux';
        $method = $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['REQUEST_SCHEME'] = 'https';
        $_SERVER['HTTP_HOST'] = 'getberlioz.com';
        $_SERVER['REDIRECT_QUERY_STRING'] = 'foo=bar&baz=qux';

        $factory = new class {
            use ServerRequestFactoryTrait;
        };
        $serverRequest = $factory->createServerRequestFromGlobals();

        $this->assertInstanceOf(Uri::class, $serverRequest->getUri());
        $this->assertEquals($uri, (string)$serverRequest->getUri());
        $this->assertEquals($method, $serverRequest->getMethod());
        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], $serverRequest->getQueryParams());
        $this->assertEquals($_SERVER, $serverRequest->getServerParams());
    }
}
