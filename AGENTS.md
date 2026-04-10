# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Bowl Of Soup Normalizer is a Symfony bundle that provides annotation-based normalization and serialization of objects. It uses an opt-in mechanism where properties/methods must be explicitly marked for normalization using annotations or PHP 8 attributes.

**Key Features:**
- Normalizes class properties and methods (public, protected, private) via annotations or PHP 8 attributes
- Supports both docblock annotations and native PHP 8 attributes (can be mixed in same codebase)
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
# Run CI with PHP 8.4
act -j build --matrix php-version:8.4

# Run all PHP versions (8.4)
act -j build

# List available jobs
act --list
```

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

### Annotation System

The bundle supports both docblock annotations and PHP 8 attributes. Both syntaxes can be used interchangeably and even mixed within the same codebase.

Three main annotations/attributes in `src/Annotation/`:

**@Normalize / #[Normalize]** - Properties/methods to include in normalization
- `name`: Output key name
- `group`: Array of context groups
- `type`: Special handling (collection, datetime, object)
- `format`: Date format for datetime types
- `callback`: Method to call for value transformation
- `normalizeCallbackResult`: Whether to normalize callback return value
- `skipEmpty`: Skip if value is empty
- `maxDepth`: Limit object nesting depth

**@Serialize / #[Serialize]** - Class-level serialization configuration
- `wrapElement`: Wraps output in a root element with this name
- `sortProperties`: Sort output keys alphabetically
- `group`: Context group for serialization

**@Translate / #[Translate]** - Translate values using Symfony translator
- `locale`: Translation locale
- `domain`: Translation domain (filename)
- `group`: Context group for translation

#### Docblock Annotation Syntax

```php
use BowlOfSoup\NormalizerBundle\Annotation as Bos;

/**
 * @Bos\Serialize(wrapElement="person", group={"api"})
 */
class Person
{
    /**
     * @Bos\Normalize(name="full_name", group={"api", "admin"})
     * @Bos\Translate(group={"api"}, domain="messages")
     */
    private ?string $name = null;

    /**
     * @Bos\Normalize(group={"admin"}, skipEmpty=true)
     */
    private ?string $email = null;

    /**
     * @Bos\Normalize(group={"api"}, type="DateTime", format="Y-m-d")
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}
```

#### PHP 8 Attribute Syntax

```php
use BowlOfSoup\NormalizerBundle\Annotation as Bos;

#[Bos\Serialize(wrapElement: 'person', group: ['api'])]
class Person
{
    #[Bos\Normalize(name: 'full_name', group: ['api', 'admin'])]
    #[Bos\Translate(group: ['api'], domain: 'messages')]
    private ?string $name = null;

    #[Bos\Normalize(group: ['admin'], skipEmpty: true)]
    private ?string $email = null;

    #[Bos\Normalize(group: ['api'], type: 'DateTime', format: 'Y-m-d')]
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }
}
```

#### Mixed Syntax (Docblock + Attributes)

Both syntaxes can coexist in the same codebase and even on the same element:

```php
/**
 * @Bos\Serialize(wrapElement="data", group={"legacy"})
 */
#[Bos\Serialize(wrapElement: 'product', group: ['api'])]
class Product
{
    /**
     * @Bos\Normalize(group={"legacy"})
     */
    private ?int $id = null;

    #[Bos\Normalize(group: ['api'], name: 'product_name')]
    private ?string $name = null;
}
```

**Important: When both exist on the same element, BOTH are processed:**
- PHP 8 attributes are read first, then docblock annotations
- If they define the **same output name**, the value will be overwritten (last processed wins - docblock)
- If they define **different names**, both will appear in the output
- **Best practice**: Avoid mixing both on the same element to prevent confusion and duplicate output

**Example of duplicate output:**
```php
/**
 * @Bos\Normalize(group={"api"}, name="docblock_name")
 */
#[Bos\Normalize(group: ['api'], name: 'attribute_name')]
private ?string $field = 'value';

// Output will contain BOTH:
// {"docblock_name": "value", "attribute_name": "value"}
```

### Extractors

Located in `src/Service/Extractor/`:
- `AnnotationExtractor`: Parses both docblock annotations (via Doctrine) and PHP 8 attributes (via Reflection) from classes/properties/methods
- `PropertyExtractor`: Extracts property metadata
- `MethodExtractor`: Extracts method metadata
- `ClassExtractor`: Coordinates extraction process

**How Annotation Reading Works:**
1. PHP 8 attributes are read first using native reflection (`ReflectionClass::getAttributes()`, etc.)
2. Docblock annotations are read second using Doctrine's `AnnotationReader`
3. Both are merged into a single array and all are processed
4. If duplicate annotations define different output names, both will appear in the final output
5. If duplicate annotations define the same output name, the last one processed wins (docblock overwrites attribute)

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

Annotations use `group` parameter to control normalization context. Properties/methods are only included when their group matches the context.

**Docblock syntax:**
```php
/**
 * @Bos\Normalize(name="email", group={"api", "admin"})
 */
private ?string $email = null;

/**
 * @Bos\Normalize(name="internalId", group={"admin"})
 */
private ?int $internalId = null;
```

**PHP 8 attribute syntax:**
```php
#[Bos\Normalize(name: 'email', group: ['api', 'admin'])]
private ?string $email = null;

#[Bos\Normalize(name: 'internalId', group: ['admin'])]
private ?int $internalId = null;
```

When normalizing with group `'api'`, only `$email` is included. When normalizing with group `'admin'`, both are included.

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
- `rector.php`: Configured for PHP 8.4 and Symfony 5.4
- `phpstan.neon.dist`: Level 3 analysis, Symfony extension enabled

## Testing

Tests use fixtures in `tests/assets/` directory:
- Traditional docblock annotations: `Person`, `Address`, `Social`, etc.
- PHP 8 attributes: `PersonWithAttributes`, `AddressWithAttributes`, `ProductWithAttributes`, `OrderWithAttributes`
- Mixed annotations: `MixedAnnotations`

The `NormalizerTestTrait` provides common test setup for service instantiation.

Test structure mirrors source: `tests/Service/`, `tests/Annotation/`, etc.

### Test Coverage for PHP 8 Attributes

The test suite includes comprehensive scenarios for PHP 8 attributes:
- Basic property and method normalization
- Translation with `#[Translate]`
- Collections and nested objects
- Context groups (api vs internal vs admin)
- Skip empty fields with `skipEmpty: true`
- Computed method values
- DateTime formatting
- Mixed docblock and attribute annotations
