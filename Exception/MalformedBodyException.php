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

namespace Berlioz\Http\Message\Exception;

use Psr\Http\Message\MessageInterface;
use RuntimeException;
use Throwable;

/**
 * Class MalformedBodyException.
 *
 * Thrown when a message body cannot be parsed into usable structured data.
 */
class MalformedBodyException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        private ?MessageInterface $httpMessage = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the HTTP message whose body is malformed.
     *
     * @return MessageInterface|null
     */
    public function getHttpMessage(): ?MessageInterface
    {
        return $this->httpMessage;
    }

    /**
     * Set the HTTP message whose body is malformed.
     *
     * @param MessageInterface $httpMessage
     *
     * @return static
     */
    public function setHttpMessage(MessageInterface $httpMessage): static
    {
        $this->httpMessage = $httpMessage;

        return $this;
    }
}
