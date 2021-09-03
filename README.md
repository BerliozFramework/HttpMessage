# Berlioz HTTP Message

[![Latest Version](https://img.shields.io/packagist/v/berlioz/http-message.svg?style=flat-square)](https://github.com/BerliozFramework/HttpMessage/releases)
[![Software license](https://img.shields.io/github/license/BerliozFramework/HttpMessage.svg?style=flat-square)](https://github.com/BerliozFramework/HttpMessage/blob/1.x/LICENSE)
[![Build Status](https://img.shields.io/travis/com/BerliozFramework/HttpMessage/1.x.svg?style=flat-square)](https://travis-ci.com/BerliozFramework/HttpMessage)
[![Build Status](https://img.shields.io/github/workflow/status/BerliozFramework/HttpMessage/Tests/1.x.svg?style=flat-square)](https://github.com/BerliozFramework/HttpMessage/actions/workflows/tests.yml?query=branch%3A1.x)
[![Quality Grade](https://img.shields.io/codacy/grade/3175956ccec64633be9a057cf549faf2/1.x.svg?style=flat-square)](https://www.codacy.com/manual/BerliozFramework/HttpMessage)
[![Total Downloads](https://img.shields.io/packagist/dt/berlioz/http-message.svg?style=flat-square)](https://packagist.org/packages/berlioz/http-message)

**Berlioz HTTP Message** is a PHP library whose implements PSR-7 (HTTP message interfaces) and PSR-17 (HTTP Factories) standards.

## Installation

### Composer

You can install **Berlioz HTTP Message** with [Composer](https://getcomposer.org/), it's the recommended installation.

```bash
$ composer require berlioz/http-message
```

### Dependencies

- **PHP** ^7.1 || ^8.0
- PHP libraries:
  - **fileinfo**
  - **json**
- Packages:
  - **psr/http-message**
  - **psr/http-factory**

## Usage

### Global

Looks at **PSR** documentations:
- **PSR-7** (HTTP message interfaces): https://www.php-fig.org/psr/psr-7/
- **PSR-17** (HTTP Factories): https://www.php-fig.org/psr/psr-17/

### Factory

Only one factory class implements the **PSR-17**:
`\Berlioz\Http\Message\HttpFactory`