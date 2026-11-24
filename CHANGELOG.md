# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `Request::HTTP_METHOD_QUERY` constant (HTTP QUERY method, RFC 10008)
- `MalformedBodyException` (extends `RuntimeException`), thrown when a message body cannot be parsed into usable data; `getHttpMessage()` returns the message whose body is malformed
- `Http1ResponseValidator`, validating the status line and header fields of any PSR-7 response for an HTTP/1
  emission without side effects (protocol version, status code 100-599, reason phrase, header names and values;
  pseudo-headers are rejected); it returns an `Http1ResponseHead` (status line, status code, headers)
- `InvalidResponseException` (extends `InvalidArgumentException`), thrown by `Http1ResponseValidator`

### Changed

- `JsonParser` throws `MalformedBodyException` on invalid JSON, and `getParsedBody()` throws it when the parsed body is neither an array, an object nor `null` (e.g. a scalar JSON root); `getParsedBody()` attaches the message to the exception when a parser does not
- Header names are validated with the same RFC 9110 field-name grammar as `Http1ResponseValidator`; pseudo-headers
  remain accepted (e.g. HAR replay)
- Leading and trailing spaces and tabs of header values are removed (optional whitespace, RFC 9110)

### Fixed

- `getParsedBody()` compares the media type case-insensitively (RFC 9110), so bodies sent as e.g. `Application/JSON` are now parsed; `addBodyParser()` registers media types in lowercase
- Header names ending with a line feed are rejected

### Security

- `MultipartStream` validates part framing metadata (GHSA-r8g6-2j96-hjg9): boundaries must follow RFC 2046 (1-70 allowed characters, no trailing space), part header names must be tokens and values must be strings without control characters (except tab), otherwise an `InvalidArgumentException` is thrown before any bytes are appended; default `Content-Disposition` `name` and `filename` parameters are escaped as in HTML forms (`"`, CR and LF percent-encoded), and backslash is percent-encoded (`%5C`) so that receivers applying quoted-pair unescaping, such as PHP, cannot read it as an escape character; `addElements()` adds no element if one of them is invalid

## [3.3.2] - 2026-10-25

### Fixed

- `getParsedBody()` no longer parses the body again when its parsed value is falsy (`null`, `[]`), and respects a `null` value set with `withParsedBody()`
- `JsonParser` returns `null` for an empty body instead of throwing a `RuntimeException`

## [3.3.1] - 2026-10-16

_No changes in this release._

## [3.3.0] - 2026-09-24

### Changed

- Generate 70-character multipart boundaries directly from `random_bytes()` instead of the generic random-string helper

## [3.2.1] - 2026-09-11

### Fixed

- `Base64Stream` now closes the base64 write filter after writing so the final quantum and padding are emitted (fixes truncated output on PHP 8.4/8.5 following php-src GH-22360)

## [3.2.0] - 2026-07-02

### Fixed

- Guard malformed `Content-Type` parsing in `getParsedBody()` (no more undefined-index warning on a type without a subtype)
- Pass an explicit nesting depth to `json_decode()` in `JsonParser`

### Security

- Reject CR/LF and invalid characters in header names/values and URI components (CRLF/header injection); HTTP/2 pseudo-headers are still allowed
- Reject CR/LF and NUL in the `Response` reason phrase (HTTP status-line injection)
- Create upload target directories with restrictive permissions (`0750`) in `UploadedFile::moveTo()`

## [3.1.1] - 2026-06-10

_No changes in this release._

## [3.1.0] - 2026-05-18

### Added

- Add `Request::HTTP_METHOD_PATCH` constant

## [3.0.0] - 2026-02-19

The version 3.0.0 marks the transition of the Berlioz Framework to a **Monorepo** structure.

Why this move?

- **Consistency:** Ensures all Berlioz components are tested and released together, preventing version mismatches.
- **Maintenance:** Simplifies the management of issues and pull requests by centralizing them in one place.
- **Better CI/CD:** Global testing ensures that a change in one package never breaks another.
