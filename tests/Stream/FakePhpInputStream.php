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

namespace Berlioz\Http\Message\Tests\Stream;

use Berlioz\Http\Message\Stream\PhpInputStream;

class FakePhpInputStream extends PhpInputStream
{
    protected const FILENAME = __DIR__ .  '/phpinput.txt';
    protected static $phpInputFp = null;

    public function getFakeStream()
    {
        return self::$phpInputFp;
    }
}