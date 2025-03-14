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

namespace Berlioz\Http\Message\Factory;

use Berlioz\Http\Message\UploadedFile;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Trait UploadedFileFactoryTrait.
 */
trait UploadedFileFactoryTrait
{
    /**
     * Create a new uploaded file.
     *
     * If a size is not provided it will be determined by checking the size of
     * the stream.
     *
     * @link http://php.net/manual/features.file-upload.post-method.php
     * @link http://php.net/manual/features.file-upload.errors.php
     *
     * @param StreamInterface $stream The underlying stream representing the uploaded file content.
     * @param int|null $size The size of the file in bytes.
     * @param int $error The PHP file upload error.
     * @param string|null $clientFilename The filename as provided by the client, if any.
     * @param string|null $clientMediaType The media type as provided by the client, if any.
     * @param string $filename Filename
     *
     * @return UploadedFileInterface
     */
    public function createUploadedFile(
        StreamInterface $stream,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null,
        string $filename = ''
    ): UploadedFileInterface {
        if (null === $size) {
            $size = $stream->getSize();
        }

        $uploadedFile = new UploadedFile($filename, $clientFilename, $clientMediaType, $size, $error);
        $uploadedFile->setStream($stream);

        return $uploadedFile;
    }
}