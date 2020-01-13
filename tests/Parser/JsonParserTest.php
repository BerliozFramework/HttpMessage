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

namespace Berlioz\Http\Message\Tests\Parser;

use Berlioz\Http\Message\Parser\JsonParser;
use Berlioz\Http\Message\Response;
use PHPUnit\Framework\TestCase;

class JsonParserTest extends TestCase
{
    public function testParseMessageBody()
    {
        $body = '{"foo": "bar"}';
        $response = new Response($body, 200, ['Content-Type' => 'application/json']);
        $parsedBody = JsonParser::parseMessageBody($response);

        $this->assertEquals(json_decode($body), $parsedBody);
    }
}
