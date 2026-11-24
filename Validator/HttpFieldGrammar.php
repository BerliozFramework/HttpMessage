<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2026 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Http\Message\Validator;

/**
 * Class HttpFieldGrammar.
 *
 * HTTP field grammar (RFC 9110, section 5).
 *
 * @internal
 */
final class HttpFieldGrammar
{
    private const TOKEN = '[!#$%&\'*+\-.^_`|~0-9A-Za-z]+';

    /**
     * Is a valid field name (token)?
     *
     * @param string $name
     *
     * @return bool
     */
    public static function isFieldName(string $name): bool
    {
        return 1 === preg_match('/^' . self::TOKEN . '$/D', $name);
    }

    /**
     * Is a valid pseudo-header field name (":" followed by a token, e.g. ":status")?
     *
     * Pseudo-header fields only exist in HTTP/2 and HTTP/3 (e.g. HAR captures),
     * they cannot be emitted in an HTTP/1 message.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function isPseudoFieldName(string $name): bool
    {
        return 1 === preg_match('/^:' . self::TOKEN . '$/D', $name);
    }

    /**
     * Is a valid field value?
     *
     * Control characters other than HTAB are forbidden, and the value must not
     * begin or end with whitespace. Empty values and obs-text are allowed.
     *
     * @param string $value
     *
     * @return bool
     */
    public static function isFieldValue(string $value): bool
    {
        if (self::hasControlCharacter($value)) {
            return false;
        }

        return 1 !== preg_match('/^[ \t]|[ \t]$/D', $value);
    }

    /**
     * Is a valid reason phrase?
     *
     * Control characters other than HTAB are forbidden. An empty reason phrase is allowed.
     *
     * @param string $reasonPhrase
     *
     * @return bool
     */
    public static function isReasonPhrase(string $reasonPhrase): bool
    {
        return !self::hasControlCharacter($reasonPhrase);
    }

    /**
     * Has a control character other than HTAB?
     *
     * @param string $str
     *
     * @return bool
     */
    private static function hasControlCharacter(string $str): bool
    {
        return 1 === preg_match('/[\x00-\x08\x0A-\x1F\x7F]/', $str);
    }
}
