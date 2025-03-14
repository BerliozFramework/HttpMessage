# Change Log

All notable changes to this project will be documented in this file. This project adheres
to [Semantic Versioning] (http://semver.org/). For change log format,
use [Keep a Changelog] (http://keepachangelog.com/).

## [2.5.0] - 2025-03-14

### Changed

- PHP 8.4 compatibility

## [2.4.0] - 2023-04-18

### Added

- Compatibility with `psr/http-message` v2

## [2.3.4] - 2022-11-17

### Fixed

- Revert of version v2.3.2, modify original URL, force replacement of spaces instead of
- Query part with not empty string but consider empty by function `empty()`
- Fragment part with not empty string but consider empty by function `empty()`
- Query not encoded with RF3986
- User infos not encoded

## [2.3.3] - 2022-09-12

### Fixed

- Keep port in "Host" header with method `Request::withUri()`

## [2.3.2] - 2022-09-08

### Fixed

- Force query parsing and rebuild for URI to prevent malformed URI query

## [2.3.1] - 2022-06-21

### Fixed

- Prevent blank spaces in URI creation

## [2.3.0] - 2022-04-07

### Added

- New method `Uri::withAddedQuery()` to add query to the existent
- New method `Uri::withoutQuery()` to remove query string name to the existent
- New method `Uri::getQueryValue()` to get query value

### Changed

- `Uri` now implement `Stringable` and `JsonSerializable` interfaces
- `Uri::create()` accept `UriInterface` parameter to convert into `Uri`
- Stream and message classes now implement `Stringable` interface

## [2.2.1] - 2022-03-30

### Fixed

- `Uri::create()` with empty scheme and string reference

## [2.2.0] - 2022-03-29

### Added

- New `MultipartStream` class
- New `Base64Stream` class
- New `AppendStream` class

### Changed

- Add type on "default" parameter of `ServerRequest::getQueryParam()` method
- `MemoryStream` allow StreamInterface or resource in argument type

### Fixed

- `Uri::create()` with empty scheme do not retrieve reference uri scheme

## [2.1.1] - 2022-02-04

### Changed

- Default value for property `path` or `Uri` is now empty

## [2.1.0] - 2021-12-23

### Added

- New method `Uri::create($uri, $ref = null): Uri` to create an uri with reference

## [2.0.2] - 2021-10-07

### Fixed

- Cast header value to string with `Message::withAddedHeader()`

## [2.0.1] - 2021-10-07

### Fixed

- Cast all header values to string to be PSR compliant

## [2.0.0] - 2021-09-08

No changes were introduced since the previous beta 4 release.

## [2.0.0-beta4] - 2021-08-30

### Added

- New `GzFileStream` class
- New `GzStream` class

### Fixed

- `Berlioz\Http\Message\Message::withHeaders()` do not replace specified headers

## [2.0.0-beta3] - 2021-06-07

### Changed

- `MemoryStream` accept initial contents in argument of constructor

## [2.0.0-beta2] - 2021-04-29

### Added

- New `FileStream` class

### Fixed

- `ServerRequest::isAjaxRequest()` compare the lower case value of `X-Requested-With` header instead of sensitive case
  value

## [2.0.0-beta1] - 2021-04-14

### Added

- Factory traits
- New method `ServerRequestFactoryTrait::createServerRequestFromGlobals()` to create server request from PHP globals

### Changed

- Bump minimum compatibility to PHP 8
- Signature of `\Berlioz\Http\Message\Request` constructor
- Signature of `\Berlioz\Http\Message\ServerRequest` constructor
- `body` parameter in constructors is now of mixed type

## [1.2.0] - 2020-11-05

### Added

- PHP 8 compatibility in `composer.json`

## [1.1.0] - 2020-03-10

### Added

- New PhpInputStream class
- All HTTP status codes and reasons phrases in Response class

## [1.0.1] - 2020-02-14

### Added

- Add http-interop/http-factory-tests package for additional tests

### Changed

- Allow null argument for reason phrase of Response constructor
- Allow null arguments in UploadedFile constructor if uploaded file is in error
- Throw InvalidArgumentException if string isn't a valid URI
- Fix HttpFactory::createStreamFromFile()

## [1.0.0] - 2020-01-14

First version
