<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Http\Message;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Response.
 *
 * @package Berlioz\Http\Message
 */
class Response extends Message implements ResponseInterface
{
    // HTTP status codes
    const HTTP_STATUS_CONTINUE = 100;
    const HTTP_STATUS_SWITCHING_PROTOCOL = 101;
    const HTTP_STATUS_PROCESSING = 102;
    const HTTP_STATUS_EARLY_HINTS = 103;
    const HTTP_STATUS_OK = 200;
    const HTTP_STATUS_CREATED = 201;
    const HTTP_STATUS_ACCEPTED = 202;
    const HTTP_STATUS_NON_AUTHORITATIVE = 203;
    const HTTP_STATUS_NO_CONTENT = 204;
    const HTTP_STATUS_RESET_CONTENT = 205;
    const HTTP_STATUS_PARTIAL_CONTENT = 206;
    const HTTP_STATUS_MULTI_STATUS = 207;
    const HTTP_STATUS_ALREADY_REPORTED = 208;
    const HTTP_STATUS_CONTENT_DIFFERENT = 210;
    const HTTP_STATUS_IM_USED = 226;
    const HTTP_STATUS_MULTIPLE_CHOICE = 300;
    const HTTP_STATUS_MOVED_PERMANENTLY = 301;
    const HTTP_STATUS_MOVED_TEMPORARILY = 302;
    const HTTP_STATUS_SEE_OTHER = 303;
    const HTTP_STATUS_NOT_MODIFIED = 304;
    const HTTP_STATUS_USE_PROXY = 305;
    const HTTP_STATUS_SWITCH_PROXY = 306;
    const HTTP_STATUS_TEMPORARY_REDIRECT = 307;
    const HTTP_STATUS_PERMANENT_REDIRECT = 308;
    const HTTP_STATUS_TOO_MANY_REDIRECTS = 310;
    const HTTP_STATUS_BAD_REQUEST = 400;
    const HTTP_STATUS_UNAUTHORIZED = 401;
    const HTTP_STATUS_PAYMENT_REQUIRED = 402;
    const HTTP_STATUS_FORBIDDEN = 403;
    const HTTP_STATUS_NOT_FOUND = 404;
    const HTTP_STATUS_METHOD_NOT_ALLOWED = 405;
    const HTTP_STATUS_NOT_ACCEPTABLE = 406;
    const HTTP_STATUS_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_STATUS_REQUEST_TIME_OUT = 408;
    const HTTP_STATUS_CONFLICT = 409;
    const HTTP_STATUS_GONE = 410;
    const HTTP_STATUS_LENGTH_REQUIRED = 411;
    const HTTP_STATUS_PRECONDITION_FAILED = 412;
    const HTTP_STATUS_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_STATUS_REQUEST_URI_TOO_LONG = 414;
    const HTTP_STATUS_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_STATUS_REQUESTED_RANGE_UNSATISFIABLE = 416;
    const HTTP_STATUS_EXPECTATION_FAILED = 417;
    const HTTP_STATUS_I_M_A_TEAPOT = 418;
    const HTTP_STATUS_BAD_MAPPING = 421;
    const HTTP_STATUS_MISDIRECTED_REQUEST = 421;
    const HTTP_STATUS_UNPROCESSABLE_ENTITY = 422;
    const HTTP_STATUS_LOCKED = 423;
    const HTTP_STATUS_METHOD_FAILURE = 424;
    const HTTP_STATUS_UNORDERED_COLLECTION = 425;
    const HTTP_STATUS_UPGRADE_REQUIRED = 426;
    const HTTP_STATUS_PRECONDITION_REQUIRED = 428;
    const HTTP_STATUS_TOO_MANY_REQUESTS = 429;
    const HTTP_STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    const HTTP_STATUS_NO_RESPONSE = 444;
    const HTTP_STATUS_RETRY_WITH = 449;
    const HTTP_STATUS_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS = 450;
    const HTTP_STATUS_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    const HTTP_STATUS_UNRECOVERABLE_ERROR = 456;
    const HTTP_STATUS_SSL_CERTIFICATE_ERROR = 495;
    const HTTP_STATUS_SSL_CERTIFICATE_REQUIRED = 496;
    const HTTP_STATUS_HTTP_REQUEST_SENT_TO_HTTPS_PORT = 497;
    const HTTP_STATUS_TOKEN_EXPIRED_OR_INVALID = 498;
    const HTTP_STATUS_CLIENT_CLOSED_REQUEST = 499;
    const HTTP_STATUS_INTERNAL_SERVER_ERROR = 500;
    const HTTP_STATUS_NOT_IMPLEMENTED = 501;
    const HTTP_STATUS_BAD_GATEWAY = 502;
    const HTTP_STATUS_SERVICE_UNAVAILABLE = 503;
    const HTTP_STATUS_GATEWAY_TIME_OUT = 504;
    const HTTP_STATUS_HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_STATUS_VARIANT_ALSO_NEGOTIATES = 506;
    const HTTP_STATUS_INSUFFICIENT_STORAGE = 507;
    const HTTP_STATUS_LOOP_DETECTED = 508;
    const HTTP_STATUS_BANDWIDTH_LIMIT_EXCEEDED = 509;
    const HTTP_STATUS_NOT_EXTENDED = 510;
    const HTTP_STATUS_NETWORK_AUTHENTICATION_REQUIRED = 511;
    const HTTP_STATUS_UNKNOWN_ERROR = 520;
    const HTTP_STATUS_WEB_SERVER_IS_DOWN = 521;
    const HTTP_STATUS_CONNECTION_TIMED_OUT = 522;
    const HTTP_STATUS_ORIGIN_IS_UNREACHABLE = 523;
    const HTTP_STATUS_A_TIMEOUT_OCCURRED = 524;
    const HTTP_STATUS_SSL_HANDSHAKE_FAILED = 525;
    const HTTP_STATUS_INVALID_SSL_CERTIFICATE = 526;
    const HTTP_STATUS_RAILGUN_ERROR = 527;
    // Reasons
    const REASONS = [
        // 1xx Informational responses
        self::HTTP_STATUS_CONTINUE => 'Continue',
        self::HTTP_STATUS_SWITCHING_PROTOCOL => 'Switching Protocols',
        self::HTTP_STATUS_PROCESSING => 'Processing',
        self::HTTP_STATUS_EARLY_HINTS => 'Early Hints',
        // 2xx Success
        self::HTTP_STATUS_OK => 'OK',
        self::HTTP_STATUS_CREATED => 'Created',
        self::HTTP_STATUS_ACCEPTED => 'Accepted',
        self::HTTP_STATUS_NON_AUTHORITATIVE => 'Non-Authoritative Information',
        self::HTTP_STATUS_NO_CONTENT => 'No Content',
        self::HTTP_STATUS_RESET_CONTENT => 'Reset Content',
        self::HTTP_STATUS_PARTIAL_CONTENT => 'Partial Content',
        self::HTTP_STATUS_MULTI_STATUS => 'Multi-Status',
        self::HTTP_STATUS_ALREADY_REPORTED => 'Already Reported',
        self::HTTP_STATUS_CONTENT_DIFFERENT => 'Content Different',
        self::HTTP_STATUS_IM_USED => 'IM Used',
        // 3xx Redirection
        self::HTTP_STATUS_MULTIPLE_CHOICE => 'Multiple Choices',
        self::HTTP_STATUS_MOVED_PERMANENTLY => 'Moved Permanently',
        self::HTTP_STATUS_MOVED_TEMPORARILY => 'Found',
        self::HTTP_STATUS_SEE_OTHER => 'See Other',
        self::HTTP_STATUS_NOT_MODIFIED => 'Not Modified',
        self::HTTP_STATUS_USE_PROXY => 'Use Proxy',
        self::HTTP_STATUS_SWITCH_PROXY => 'Switch Proxy',
        self::HTTP_STATUS_TEMPORARY_REDIRECT => 'Temporary Redirect',
        self::HTTP_STATUS_PERMANENT_REDIRECT => 'Permanent Redirect',
        self::HTTP_STATUS_TOO_MANY_REDIRECTS => 'Too many Redirects',
        // 4xx Client errors
        self::HTTP_STATUS_BAD_REQUEST => 'Bad Request',
        self::HTTP_STATUS_UNAUTHORIZED => 'Unauthorized',
        self::HTTP_STATUS_PAYMENT_REQUIRED => 'Payment Required',
        self::HTTP_STATUS_FORBIDDEN => 'Forbidden',
        self::HTTP_STATUS_NOT_FOUND => 'Not Found',
        self::HTTP_STATUS_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::HTTP_STATUS_NOT_ACCEPTABLE => 'Not Acceptable',
        self::HTTP_STATUS_PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        self::HTTP_STATUS_REQUEST_TIME_OUT => 'Request Time-out',
        self::HTTP_STATUS_CONFLICT => 'Conflict',
        self::HTTP_STATUS_GONE => 'Gone',
        self::HTTP_STATUS_LENGTH_REQUIRED => 'Length Required',
        self::HTTP_STATUS_PRECONDITION_FAILED => 'Precondition Failed',
        self::HTTP_STATUS_REQUEST_ENTITY_TOO_LARGE => 'Request Entity Too Large',
        self::HTTP_STATUS_REQUEST_URI_TOO_LONG => 'Request-URI Too Long',
        self::HTTP_STATUS_UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        self::HTTP_STATUS_REQUESTED_RANGE_UNSATISFIABLE => 'Requested range unsatisfiable',
        self::HTTP_STATUS_EXPECTATION_FAILED => 'Expectation Failed',
        self::HTTP_STATUS_I_M_A_TEAPOT => 'I\'m a teapot',
        self::HTTP_STATUS_MISDIRECTED_REQUEST => 'Bad mapping / Misdirected Request',
        self::HTTP_STATUS_UNPROCESSABLE_ENTITY => 'Unprocessable entity',
        self::HTTP_STATUS_LOCKED => 'Locked',
        self::HTTP_STATUS_METHOD_FAILURE => 'Method failure',
        self::HTTP_STATUS_UNORDERED_COLLECTION => '	Unordered Collection',
        self::HTTP_STATUS_UPGRADE_REQUIRED => 'Upgrade Required',
        self::HTTP_STATUS_PRECONDITION_REQUIRED => 'Precondition Required',
        self::HTTP_STATUS_TOO_MANY_REQUESTS => 'Too Many Requests',
        self::HTTP_STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
        self::HTTP_STATUS_NO_RESPONSE => 'No Response',
        self::HTTP_STATUS_RETRY_WITH => 'Retry With',
        self::HTTP_STATUS_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS => 'Blocked by Windows Parental Controls',
        self::HTTP_STATUS_UNAVAILABLE_FOR_LEGAL_REASONS => 'Unavailable For Legal Reasons',
        self::HTTP_STATUS_UNRECOVERABLE_ERROR => 'Unrecoverable Error',
        self::HTTP_STATUS_SSL_CERTIFICATE_ERROR => 'SSL Certificate Error',
        self::HTTP_STATUS_SSL_CERTIFICATE_REQUIRED => 'SSL Certificate Required',
        self::HTTP_STATUS_HTTP_REQUEST_SENT_TO_HTTPS_PORT => 'HTTP Request Sent to HTTPS Port',
        self::HTTP_STATUS_TOKEN_EXPIRED_OR_INVALID => 'Token expired/invalid',
        self::HTTP_STATUS_CLIENT_CLOSED_REQUEST => 'Client Closed Request',
        // 5xx Server error
        self::HTTP_STATUS_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::HTTP_STATUS_NOT_IMPLEMENTED => 'Not Implemented',
        self::HTTP_STATUS_BAD_GATEWAY => 'Bad Gateway',
        self::HTTP_STATUS_SERVICE_UNAVAILABLE => 'Service Unavailable',
        self::HTTP_STATUS_GATEWAY_TIME_OUT => 'Gateway Time-out',
        self::HTTP_STATUS_HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version not supported',
        self::HTTP_STATUS_VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
        self::HTTP_STATUS_INSUFFICIENT_STORAGE => 'Insufficient storage',
        self::HTTP_STATUS_LOOP_DETECTED => 'Loop Detected',
        self::HTTP_STATUS_BANDWIDTH_LIMIT_EXCEEDED => 'Bandwidth Limit Exceeded',
        self::HTTP_STATUS_NOT_EXTENDED => 'Not Extended',
        self::HTTP_STATUS_NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
        self::HTTP_STATUS_UNKNOWN_ERROR => 'Unknown Error',
        self::HTTP_STATUS_WEB_SERVER_IS_DOWN => 'Web Server Is Down',
        self::HTTP_STATUS_CONNECTION_TIMED_OUT => 'Connection Timed Out',
        self::HTTP_STATUS_ORIGIN_IS_UNREACHABLE => 'Origin Is Unreachable',
        self::HTTP_STATUS_A_TIMEOUT_OCCURRED => 'A Timeout Occurred',
        self::HTTP_STATUS_SSL_HANDSHAKE_FAILED => 'SSL Handshake Failed',
        self::HTTP_STATUS_INVALID_SSL_CERTIFICATE => 'Invalid SSL Certificate',
        self::HTTP_STATUS_RAILGUN_ERROR => 'Railgun Error',
    ];
    /** @var int Status code */
    protected $statusCode;
    /** @var string Reason phrase */
    protected $reasonPhrase;

    /**
     * Response constructor.
     *
     * @param \Psr\Http\Message\StreamInterface|string|null $body Body
     * @param int $statusCode Status code
     * @param array $headers Headers
     * @param string|null $reasonPhrase Reason phrase
     */
    public function __construct($body = null, int $statusCode = 200, array $headers = [], ?string $reasonPhrase = '')
    {
        $this->body = new Stream;

        if (null !== $body) {
            if ($body instanceof StreamInterface) {
                $this->body = $body;
            }

            if (is_scalar($body)) {
                $this->body->write($body);
            }
        }

        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase ?? '';

        $this->headers = [];
        $this->setHeaders($headers);
    }

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @param int $code The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification.
     *
     * @return static
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $clone = clone $this;
        $clone->statusCode = $code;
        $clone->reasonPhrase = $reasonPhrase;

        return $clone;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        return $this->reasonPhrase ?: self::REASONS[$this->statusCode] ?? '';
    }
}