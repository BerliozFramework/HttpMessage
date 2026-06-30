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

namespace Berlioz\Http\Message;

use Berlioz\Http\Message\Factory\RequestFactoryTrait;
use Berlioz\Http\Message\Factory\ResponseFactoryTrait;
use Berlioz\Http\Message\Factory\ServerRequestFactoryTrait;
use Berlioz\Http\Message\Factory\StreamFactoryTrait;
use Berlioz\Http\Message\Factory\UploadedFileFactoryTrait;
use Berlioz\Http\Message\Factory\UriFactoryTrait;
use Psr\Http\Message as Psr;

/**
 * Class HttpFactory.
 */
class HttpFactory implements
    Psr\RequestFactoryInterface,
    Psr\ResponseFactoryInterface,
    Psr\ServerRequestFactoryInterface,
    Psr\StreamFactoryInterface,
    Psr\UploadedFileFactoryInterface,
    Psr\UriFactoryInterface
{
    use RequestFactoryTrait;
    use ResponseFactoryTrait;
    use ServerRequestFactoryTrait;
    use StreamFactoryTrait;
    use UploadedFileFactoryTrait;
    use UriFactoryTrait;
}
