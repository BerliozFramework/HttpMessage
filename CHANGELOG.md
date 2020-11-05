# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning] (http://semver.org/).
For change log format, use [Keep a Changelog] (http://keepachangelog.com/).

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