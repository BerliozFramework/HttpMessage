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

use Berlioz\Http\Message\Factory\ResponseFactoryTrait;
use Berlioz\Http\Message\Response;
use Berlioz\Http\Message\Stream\MemoryStream;
use PHPUnit\Framework\TestCase;

class ResponseFactoryTraitTest extends TestCase
{
    public function testCreateResponse()
    {
        $factory = new class {
            use ResponseFactoryTrait;
        };
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
}
