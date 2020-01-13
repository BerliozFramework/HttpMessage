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

declare(strict_types=1);

namespace Berlioz\Http\Message\Parser;

use Psr\Http\Message\MessageInterface;

/**
 * Class FormUrlEncodedParser.
 *
 * @package Berlioz\Http\Message\Parser
 */
class FormUrlEncodedParser implements ParserInterface
{
    /**
     * @inheritDoc
     */
    public static function parseMessageBody(MessageInterface $message)
    {
        $parsedBody = [];
        parse_str((string)$message->getBody(), $parsedBody);

        return $parsedBody;
    }
}