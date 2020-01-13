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

use Berlioz\Http\Message\Response;
use Berlioz\Http\Message\Stream;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function testConstructAndGetters()
    {
        $body = new Stream();
        $body->write('{"foo": "bar"}');

        $response = new Response($body, 300, $headers = ['Content-Type' => ['application/json']]);

        $this->assertEquals(300, $response->getStatusCode());
        $this->assertEquals($headers, $response->getHeaders());
        $this->assertEquals($body, $response->getBody());
    }

    public function testWithStatus()
    {
        $response = new Response(null, 200);
        $response2 = $response->withStatus(Response::HTTP_STATUS_NOT_FOUND, $reason = 'Foo not found');

        $this->assertNotEquals($response, $response2);
        $this->assertEquals(Response::HTTP_STATUS_OK, $response->getStatusCode());
        $this->assertEquals(Response::HTTP_STATUS_NOT_FOUND, $response2->getStatusCode());
        $this->assertEquals($reason, $response2->getReasonPhrase());
    }
}
