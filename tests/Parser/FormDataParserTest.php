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

use Berlioz\Http\Message\Parser\FormDataParser;
use Berlioz\Http\Message\Response;
use PHPUnit\Framework\TestCase;

class FormDataParserTest extends TestCase
{
    public function testParseMessageBody()
    {
        $_POST = $exceptedResult = ['foo' => 'bar'];
        $response = new Response(null, 200, ['Content-Type' => 'multipart/form-data']);
        $parsedBody = FormDataParser::parseMessageBody($response);

        $this->assertEquals($exceptedResult, $parsedBody);
    }
}
