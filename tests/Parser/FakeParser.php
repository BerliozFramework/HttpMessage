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

use Berlioz\Http\Message\Parser\ParserInterface;
use Psr\Http\Message\MessageInterface;

class FakeParser implements ParserInterface
{
    /**
     * @inheritDoc
     */
    public static function parseMessageBody(MessageInterface $message)
    {
        return json_decode($message->getBody(), JSON_OBJECT_AS_ARRAY);
    }
}