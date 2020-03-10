<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 * @author    Yohann Lorant <https://github.com/ylorant>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * Class UploadedFile.
 *
 * @package Berlioz\Http\Message
 */
class UploadedFile implements UploadedFileInterface
{
    /** @var string File */
    protected $file;
    /** @var string Name of file */
    protected $name;
    /** @var string Mime type of file */
    protected $type;
    /** @var int Size of file */
    protected $size;
    /** @var int PHP UPLOAD_ERR_xxx error code */
    protected $error;
    /** @var bool If file has already moved */
    protected $moved = false;
    /** @var \Berlioz\Http\Message\Stream Stream of uploaded file */
    private $stream;

    /**
     * Parse uploaded files from $_FILES PHP environment variable
     *
     * @param array $uploadedFiles $_FILES value
     *
     * @return array Multi dimensional \Berlioz\Http\Message\UploadedFile array
     */
    public static function parseUploadedFiles(array $uploadedFiles): array
    {
        if (!function_exists(__NAMESPACE__ . '\createUploadedFileObj')) {
            function createUploadedFileObj($array)
            {
                $result = [];

                foreach ($array as $key => $value) {
                    if (is_array($value)) {
                        $result[$key] = createUploadedFileObj($value);
                        continue;
                    }

                    if ($array['error'] && $array['error'] == 4) {
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
        }

        // Result
        $normalized = self::sanitizeFileData($uploadedFiles);
        $result = createUploadedFileObj($normalized);
        if (is_array($result)) {
            return $result;
        }

        return [];
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
    public static function sanitizeFileData($files)
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
                self::filesFlip($res, [], $data);
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
     * @return void
     *
     * @author Bart Kelsey <http://www.opengameart.org>
     * @see    https://stackoverflow.com/questions/5444827/how-do-you-loop-through-files-array/29664753#29664753
     */
    public static function filesFlip(&$result, $keys, $value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $newKeys = $keys;
                array_push($newKeys, $k);
                self::filesFlip($result, $newKeys, $v);
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

    /**
     * UploadedFile constructor
     *
     * @param string $file File
     * @param string $name Client name of file
     * @param string $type Client type of file
     * @param int $size Size
     * @param int $error PHP error code
     */
    public function __construct(string $file, ?string $name, ?string $type, ?int $size, int $error)
    {
        $this->file = $file;
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->error = $error;
    }

    /**
     * If uploaded file has been moved
     *
     * @return bool
     */
    public function hasMoved()
    {
        return $this->moved;
    }

    /**
     * Is uploaded file?
     *
     * @return bool
     */
    public function isUploadedFile(): bool
    {
        return is_uploaded_file($this->file);
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException in cases when no stream is available or can be
     *     created.
     */
    public function getStream()
    {
        if ($this->hasMoved()) {
            throw new RuntimeException(sprintf('Uploaded file "%s" has already moved', $this->file));
        }

        if (null === $this->stream) {
            $this->stream = new Stream(fopen($this->file, 'r'));
        }

        return $this->stream;
    }

    /**
     * Set stream of uploaded file.
     *
     * @param \Psr\Http\Message\StreamInterface $stream
     *
     * @return \Berlioz\Http\Message\UploadedFile
     */
    public function setStream(StreamInterface $stream): UploadedFile
    {
        if ($this->hasMoved()) {
            throw new RuntimeException(sprintf('Uploaded file "%s" has already moved', $this->file));
        }

        $this->stream = $stream;

        return $this;
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     *
     * @param string $targetPath Path to which to move the uploaded file.
     *
     * @throws \InvalidArgumentException if the $targetPath specified is invalid.
     * @throws \RuntimeException on any error during the move operation, or on
     *     the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        if ($this->hasMoved()) {
            throw new RuntimeException(sprintf('Uploaded file "%s" has already moved', $this->file));
        }

        $directory = dirname($targetPath);

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0777, true)) {
                throw new RuntimeException(sprintf('Error during directory creation "%s"', $directory));
            }
        }

        if (!is_writable($directory)) {
            throw new InvalidArgumentException(sprintf('Target path "%s" is not writable', $directory));
        }

        if (!$this->isUploadedFile()) {
            throw new RuntimeException(sprintf('"%s" is not a valid uploaded file', $this->file));
        }

        $this->moved = move_uploaded_file($this->file, $targetPath);
    }

    /**
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Retrieve the hash value using the contents of file.
     *
     * @param string $algo Name of selected hashing algorithm (i.e. "md5", "sha256", "haval160,4", etc..)
     *
     * @return string
     * @throws \RuntimeException on any error during the hash operation.
     *
     * @see \hash_file()
     */
    public function getHash($algo = 'sha1')
    {
        if ($this->hasMoved()) {
            throw new RuntimeException(sprintf('Uploaded file "%s" has already moved', $this->file));
        }

        return hash_file($algo, $this->file);
    }

    /**
     * Retrieve the media type of file.
     *
     * @return string|null The media type or null if unavailable
     * @throws \RuntimeException on any error during the mime extraction operation.
     */
    public function getMediaType()
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($this->hasMoved()) {
            throw new RuntimeException(sprintf('Uploaded file "%s" has already moved', $this->file));
        }

        if (!extension_loaded('fileinfo')) {
            throw new RuntimeException(
                sprintf(
                    'You must install fileinfo extension to determine the real type of uploaded file "%s"',
                    $this->file
                )
            );
        }

        $finfo = finfo_open(FILEINFO_MIME);
        $mime = finfo_file($finfo, $this->file);
        finfo_close($finfo);

        $mime = explode(";", $mime);
        $mime = trim($mime[0]);

        return $mime;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return string|null The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType()
    {
        return $this->type;
    }
}
