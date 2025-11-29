# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Bowl Of Soup Normalizer is a Symfony bundle that provides annotation-based normalization and serialization of objects. It uses an opt-in mechanism where properties/methods must be explicitly marked for normalization using annotations.

**Key Features:**
- Normalizes class properties and methods (public, protected, private) via annotations
- Handles Doctrine proxy objects and circular references
- Object caching via `getId()` method to avoid re-normalizing same objects
- Annotation caching (per normalize command and permanent in prod mode)
- Supports Symfony translations with locale and domain configuration
- Context groups for different normalization scenarios

## Development Commands

### Running Tests Directly

```bash
# Run all tests
vendor/bin/phpunit

# Run tests with coverage (requires Xdebug)
XDEBUG_MODE=coverage php -dzend_extension=xdebug.so vendor/bin/phpunit
# Coverage output: tests/coverage/

# Run specific test file
vendor/bin/phpunit tests/Service/NormalizerTest.php
```

### Code Quality Tools

```bash
# Static analysis (level 3)
vendor/bin/phpstan

# Code style fixing
vendor/bin/php-cs-fixer fix

# Automated refactoring (dry-run recommended first)
vendor/bin/rector process --dry-run --no-progress-bar --ansi

# Apply rector changes
vendor/bin/rector process
```

### Running GitHub Actions Locally with act

You can run the complete CI pipeline locally using [act](https://nektosact.com). 
If act isn't installed, try to install it (on macOS via homebrew).

```bash
# Run CI with specific PHP version
act -j build --matrix php-version:7.4

# Run CI with PHP 8.2
act -j build --matrix php-version:8.2

# Run all PHP versions (7.2, 7.4, 8.2)
act -j build

# List available jobs
act --list
```

**Codecov Token**: To upload coverage to Codecov, create a `.env.local` file in the project root (copy from `.env`):
```bash
cp .env .env.local
# Edit .env.local and add your token:
CODECOV_TOKEN=your-codecov-token-here
```
The `.env.local` file is gitignored and will be automatically picked up by act. The `.env` file is committed as a template.

**Note**: The first run will be slow as it downloads the container image and sets up PHP. Subsequent runs are much faster due to container reuse (configured in `~/Library/Application Support/act/actrc`).

**Configuration**: act is configured to:
- Use `catthehacker/ubuntu:act-latest` as the base image (medium size)
- Reuse containers between runs for faster execution
- Use linux/amd64 architecture (required for M-series Macs)
- Read environment variables from `.env.local` only (`.env` is just a template)

## Architecture

### Core Components

**Normalizer** (`src/Service/Normalizer.php`)
- Entry point for normalization operations
- Handles both single objects and collections
- Manages ObjectCache for circular reference detection
- Delegates to PropertyNormalizer and MethodNormalizer

**Serializer** (`src/Service/Serializer.php`)
- Wraps Normalizer with encoding capabilities (JSON, XML)
- Uses EncoderFactory to create encoders
- Supports sorting via `@Serialize` annotation

**ObjectCache** (`src/Model/ObjectCache.php`)
- Static cache preventing circular references
- Caches normalized results by object name and identifier (from `getId()`)
- Must be cleared between normalize operations

### Annotation System

Three main annotations in `src/Annotation/`:

**@Normalize** - Properties/methods to include in normalization
- `name`: Output key name
- `group`: Array of context groups
- `type`: Special handling (collection, datetime, object)
- `format`: Date format for datetime types
- `callback`: Method to call for value transformation
- `normalizeCallbackResult`: Whether to normalize callback return value
- `skipEmpty`: Skip if value is empty
- `maxDepth`: Limit object nesting depth

**@Serialize** - Class-level serialization configuration
- `sortProperties`: Sort output keys alphabetically
- `group`: Context group for serialization

**@Translate** - Translate values using Symfony translator
- `locale`: Translation locale
- `domain`: Translation domain (filename)

### Extractors

Located in `src/Service/Extractor/`:
- `AnnotationExtractor`: Parses annotations from classes/properties/methods
- `PropertyExtractor`: Extracts property metadata
- `MethodExtractor`: Extracts method metadata
- `ClassExtractor`: Coordinates extraction process

### Normalizers

Located in `src/Service/Normalize/`:
- `PropertyNormalizer`: Normalizes object properties
- `MethodNormalizer`: Normalizes method return values
- Both extend `AbstractNormalizer` which handles type-specific normalization logic

### Encoders

Located in `src/Service/Encoder/`:
- `EncoderJson`: JSON encoding
- `EncoderXml`: XML encoding
- `EncoderFactory`: Creates encoder instances by type string

## Important Patterns

### Context Groups
Annotations use `group` parameter to control normalization context:
```php
@Normalize(name="email", group={"api", "admin"})
@Normalize(name="internalId", group={"admin"})
```
When normalizing, pass group to include only matching annotations.

### Circular Reference Handling
Objects implementing `getId()` are cached and reused. If a circular reference is detected, the object's ID value is returned instead of re-normalizing.

### Doctrine Proxy Support
The bundle handles Doctrine proxy objects by extracting the real class name before processing.

### Additional PHP Coding Standards

#### General PHP standards

- `declare(strict_types=1);` MUST be declared at the top for *new* PHP files
- Short array notation MUST be used
- Use Monolog for all logging operations (Psr\Log\LoggerInterface)
- Declare statements MUST be terminated by a semicolon
- Classes from the global namespace MUST NOT be imported (but prefixed with \)
- Import statements MUST be alphabetized
- The PHP features "parameter type widening" and "contravariant argument types" MUST NOT be used
- Boolean operators between conditions MUST always be at the beginning of the line
- The concatenation operator MUST be preceded and followed by one space
- Do not use YODA conditions
- All boolean API property names MUST be prepended with "is", "has" or "should"

#### PHPDoc

- There MUST NOT be an @author, @version or @copyright tag
- Extended type information SHOULD be used when possible (PHPStan array types)
- Arguments MUST be documented as relaxed as possible, while return values MUST be documented as precise as possible
- The @param tag MUST be omitted when the argument is properly type-hinted
- The @return tag MUST be omitted when the method or function has a proper return type-hint
- An entire docblock MUST be omitted in case it does not add "any value" over the method name, argument types and return type
- Constants MUST NOT be documented using the @var tag

#### PHPUnit

- We use Mockery for mocking, so all tests that use Mockery MUST extend Mockery\Adapter\Phpunit\MockeryTestCase
- Unit tests MUST not use the `@testdox` tag, but use a descriptive human-readable test method name instead
- Data providers MUST be used when possible and applicable
- All unit test class members MUST be unset in the `tearDown()` method

## Configuration Files

- `phpunit.xml`: Test configuration, excludes DI/EventListener/Exception/Model from coverage
- `.php-cs-fixer.php`: Custom finder supporting Git diff, STDIN, or CLI input; PSR-2/Symfony standards
- `rector.php`: Configured for PHP 7.2+ and Symfony 5.4
- `phpstan.neon.dist`: Level 3 analysis, Symfony extension enabled

## Testing

Tests use fixtures in `tests/assets/` directory (Person, Address, Social, etc.). The `NormalizerTestTrait` provides common test setup for service instantiation.

Test structure mirrors source: `tests/Service/`, `tests/Annotation/`, etc.
