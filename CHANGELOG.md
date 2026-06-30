# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
