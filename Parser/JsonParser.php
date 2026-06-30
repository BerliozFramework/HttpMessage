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

use JsonException;
use Psr\Http\Message\MessageInterface;
use RuntimeException;

/**
 * Class JsonParser.
 */
class JsonParser implements ParserInterface
{
    /**
     * @inheritDoc
     */
    /** Maximum nesting depth allowed when decoding JSON (PHP default). */
    public const MAX_DEPTH = 512;

    /**
     * @inheritDoc
     */
    public static function parseMessageBody(MessageInterface $message): mixed
    {
        try {
            return json_decode(
                (string)$message->getBody(),
                depth: self::MAX_DEPTH,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new RuntimeException('Cannot parse JSON contents', previous: $exception);
        }
    }
}