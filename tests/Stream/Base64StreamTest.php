<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2022 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Http\Message\Tests\Stream;

use Berlioz\Http\Message\Stream\Base64Stream;
use PHPUnit\Framework\TestCase;

class Base64StreamTest extends TestCase
{
    public function test()
    {
        $stream = new Base64Stream('foo bar');

        $this->assertEquals(12, $stream->getSize());
        $this->assertEquals('Zm9vIGJhcg==', $stream->getContents());
    }

    public function test_multipleLine()
    {
        $stream = new Base64Stream(
            "Sin autem ad adulescentiam perduxissent, dirimi tamen interdum contentione\r\n" .
            "vel uxoriae condicionis vel commodi alicuius, quod idem adipisci uterque\r\n" .
            "non posset. Quod si qui longius in amicitia provecti essent, tamen saepe\r\n" .
            "labefactari, si in honoris contentionem incidissent; pestem enim nullam\r\n" .
            "maiorem esse amicitiis quam in plerisque pecuniae cupiditatem, in optimis\r\n" .
            "quibusque honoris certamen et gloriae; ex quo inimicitias maximas saepe\r\n" .
            "inter amicissimos exstitisse."
        );

        $this->assertEquals(648, $stream->getSize());
        $this->assertEquals(
            "U2luIGF1dGVtIGFkIGFkdWxlc2NlbnRpYW0gcGVyZHV4aXNzZW50LCBkaXJpbWkgdGFtZW4gaW50\r\n" .
            "ZXJkdW0gY29udGVudGlvbmUNCnZlbCB1eG9yaWFlIGNvbmRpY2lvbmlzIHZlbCBjb21tb2RpIGFs\r\n" .
            "aWN1aXVzLCBxdW9kIGlkZW0gYWRpcGlzY2kgdXRlcnF1ZQ0Kbm9uIHBvc3NldC4gUXVvZCBzaSBx\r\n" .
            "dWkgbG9uZ2l1cyBpbiBhbWljaXRpYSBwcm92ZWN0aSBlc3NlbnQsIHRhbWVuIHNhZXBlDQpsYWJl\r\n" .
            "ZmFjdGFyaSwgc2kgaW4gaG9ub3JpcyBjb250ZW50aW9uZW0gaW5jaWRpc3NlbnQ7IHBlc3RlbSBl\r\n" .
            "bmltIG51bGxhbQ0KbWFpb3JlbSBlc3NlIGFtaWNpdGlpcyBxdWFtIGluIHBsZXJpc3F1ZSBwZWN1\r\n" .
            "bmlhZSBjdXBpZGl0YXRlbSwgaW4gb3B0aW1pcw0KcXVpYnVzcXVlIGhvbm9yaXMgY2VydGFtZW4g\r\n" .
            "ZXQgZ2xvcmlhZTsgZXggcXVvIGluaW1pY2l0aWFzIG1heGltYXMgc2FlcGUNCmludGVyIGFtaWNp\r\n" .
            "c3NpbW9zIGV4c3RpdGlzc2Uu",
            $stream->getContents()
        );
    }
}
