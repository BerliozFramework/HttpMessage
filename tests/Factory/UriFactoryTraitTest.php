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

namespace Berlioz\Http\Message\Tests\Factory;

use Berlioz\Http\Message\Factory\UriFactoryTrait;
use PHPUnit\Framework\TestCase;

class UriFactoryTraitTest extends TestCase
{
    public function testCreateUri()
    {
        $factory = new class {
            use UriFactoryTrait;
        };
        $uri = $factory->createUri('https://getberlioz.com/foo/bar?test=value#hash');

        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('getberlioz.com', $uri->getHost());
        $this->assertEquals('/foo/bar', $uri->getPath());
        $this->assertEquals('test=value', $uri->getQuery());
        $this->assertEquals('hash', $uri->getFragment());
    }
}
