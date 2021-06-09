# Change Log

All notable changes to this project will be documented in this file. This project adheres
to [Semantic Versioning] (http://semver.org/). For change log format,
use [Keep a Changelog] (http://keepachangelog.com/).

## [2.0.0-beta4] - In progress

### Fixed

- `Berlioz\Http\Message\Message::withHeaders()` do not replace specified headers

## [2.0.0-beta3] - 2021-06-07

### Changed

- `MemoryStream` accept initial contents in argument of constructor

## [2.0.0-beta2] - 2021-04-29

### Added

- New `FileStream` class

### Fixed

- `ServerRequest::isAjaxRequest()` compare the lower case value of `X-Requested-With` header instead of sensitive case value

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