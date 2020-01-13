# Berlioz HTTP Message

**Berlioz HTTP Message** is a PHP library whose implements PSR-7 (HTTP message interfaces) and PSR-17 (HTTP Factories) standards.

## Installation

### Composer

You can install **Berlioz HTTP Message** with [Composer](https://getcomposer.org/), it's the recommended installation.

```bash
$ composer require berlioz/http-message
```

### Dependencies

- **PHP** >= 7.1
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