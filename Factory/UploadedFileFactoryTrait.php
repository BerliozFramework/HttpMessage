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

        return new UploadedFile(
            $filename,
            $clientFilename,
            $clientMediaType,
            $size,
            $error,
            $stream,
        );
    }

    /**
     * Parse uploaded files from $_FILES format.
     *
     * @param array $uploadedFiles
     *
     * @return UploadedFileInterface[]
     */
    public function createUploadedFiles(array $uploadedFiles): array
    {
        // Result
        $normalized = $this->sanitizeFileData($uploadedFiles);
        $result = $this->createUploadedFileFromArray($normalized);

        if (is_array($result)) {
            return $result;
        }

        return [];
    }

    /**
     * Create uploaded file from array.
     *
     * @param array $array
     *
     * @return UploadedFileInterface|UploadedFileInterface[]
     */
    protected function createUploadedFileFromArray(array $array): UploadedFileInterface|array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->createUploadedFileFromArray($value);
                continue;
            }

            if (isset($array['error']) && $array['error'] == 4) {
                continue;
            }

            return new UploadedFile(
                $array['tmp_name'],
                $array['name'] ?: '',
                $array['type'] ?: '',
                $array['size'] ?: 0,
                $array['error'] ?: 0
            );
        }

        return $result;
    }

    /**
     * Sanitizes the data from $_FILES and returns them as a proper form-input style value, with recursive support.
     *
     * @param array $files The $_FILES array to sanitize.
     *
     * @return array
     *
     * @author Bart Kelsey <http://www.opengameart.org>
     * @see    https://stackoverflow.com/questions/5444827/how-do-you-loop-through-files-array/29664753#29664753
     */
    protected function sanitizeFileData(array $files): array
    {
        $result = [];

        foreach ($files as $field => $data) {
            foreach ($data as $val) {
                $result[$field] = [];

                if (!is_array($val)) {
                    $result[$field] = $data;
                    continue;
                }

                $res = [];
                $this->filesFlip($res, [], $data);
                $result[$field] += $res;
            }
        }

        return $result;
    }

    /**
     * Flips the file's array keys.
     *
     * @param array $result
     * @param array $keys
     * @param mixed $value
     *
     * @author Bart Kelsey <http://www.opengameart.org>
     * @see    https://stackoverflow.com/questions/5444827/how-do-you-loop-through-files-array/29664753#29664753
     */
    protected function filesFlip(array &$result, array $keys, mixed $value): void
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $newKeys = $keys;
                array_push($newKeys, $k);
                $this->filesFlip($result, $newKeys, $v);
            }

            return;
        }

        $res = $value;
        // Move the innermost key to the outer spot
        $first = array_shift($keys);
        array_push($keys, $first);
        foreach (array_reverse($keys) as $k) {
            // You might think we'd say $res[$k] = $res, but $res starts out not as an array
            $res = [$k => $res];
        }

        $result = array_replace_recursive($result, $res);
    }
}
