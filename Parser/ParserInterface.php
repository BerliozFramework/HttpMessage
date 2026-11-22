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

declare(strict_types=1);

namespace Berlioz\Http\Message\Parser;

use Berlioz\Http\Message\Exception\MalformedBodyException;
use Psr\Http\Message\MessageInterface;

/**
 * Interface ParserInterface.
 */
interface ParserInterface
{
    /**
     * Parse message.
     *
     * @param MessageInterface $message
     *
     * @return mixed
     * @throws MalformedBodyException If contents cannot be parsed.
     */
    public static function parseMessageBody(MessageInterface $message): mixed;
}