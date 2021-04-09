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

use Berlioz\Http\Message\Factory\RequestFactoryTrait;
use Berlioz\Http\Message\Request;
use Berlioz\Http\Message\Stream\MemoryStream;
use Berlioz\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class RequestFactoryTraitTest extends TestCase
{
    public function testCreateRequest()
    {
        $factory = new class {
            use RequestFactoryTrait;
        };
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
}
