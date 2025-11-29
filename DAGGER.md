# Dagger CI Pipeline

This project uses [Dagger](https://dagger.io) for CI/CD, with a PHP-based pipeline that runs both locally and in GitHub Actions.

## Quick Start

### Run the complete CI pipeline locally

```bash
# Using the helper script (easiest)
./dagger-ci.sh

# Or with a specific PHP version
./dagger-ci.sh test 8.2

# Or using dagger directly
dagger call test --source=. --php-version=7.4
```

## Available Commands

### Helper Script (`./dagger-ci.sh`)

```bash
./dagger-ci.sh [command] [php-version]

Commands:
  test              Run all CI checks (PHPStan + PHPUnit) - default
  phpunit           Run only PHPUnit tests
  phpstan           Run only PHPStan analysis
  rector            Run only Rector checks
  php-cs-fixer      Run only PHP-CS-Fixer
  coverage          Generate code coverage report (HTML + Clover XML)
  test-all          Run tests on all PHP versions (7.2, 7.4, 8.2)

Examples:
  ./dagger-ci.sh                    # Run all checks with PHP 7.4
  ./dagger-ci.sh test 8.2           # Run all checks with PHP 8.2
  ./dagger-ci.sh phpunit 7.4        # Run only tests with PHP 7.4
  ./dagger-ci.sh coverage           # Generate coverage (saves to tests/coverage/)
  ./dagger-ci.sh test-all           # Run on all PHP versions
```

### Direct Dagger Commands

```bash
# Run all checks
dagger call test --source=. --php-version=7.4

# Run individual checks
dagger call phpunit --source=. --php-version=8.2
dagger call phpstan --source=. --php-version=7.4
dagger call rector --source=. --php-version=7.4
dagger call php-cs-fixer --source=. --php-version=7.4

# Generate coverage and export to local directory
dagger call phpunit-coverage --source=. --php-version=8.2 export --path=./tests/coverage

# Run on all PHP versions
dagger call test-matrix --source=.

# List all available functions
dagger functions
```

## Code Coverage

### Local Coverage Reports

Generate coverage reports locally:

```bash
# Using helper script (easiest)
./dagger-ci.sh coverage

# Or using dagger directly
dagger call phpunit-coverage --source=. --php-version=8.2 export --path=./tests/coverage
```

This will:
- Run PHPUnit with Xdebug coverage enabled
- Generate HTML report at `tests/coverage/index.html`
- Generate Clover XML at `tests/coverage/clover.xml`

Open the HTML report in your browser:
```bash
open tests/coverage/index.html  # macOS
xdg-open tests/coverage/index.html  # Linux
```

### GitHub Actions + Codecov

Coverage is automatically generated and uploaded to Codecov on every push/PR:
1. Tests run with coverage on PHP 8.2
2. Clover XML is exported
3. Uploaded to Codecov using `codecov/codecov-action@v4`

**Required Secret**: Add `CODECOV_TOKEN` to your GitHub repository secrets.

Get your token from: https://codecov.io/gh/BowlOfSoup/NormalizerBundle

## Supported PHP Versions

- PHP 7.2
- PHP 7.4 (default)
- PHP 8.2

## Pipeline Configuration

The pipeline is defined in `.dagger/src/NormalizerBundle.php` using PHP with Dagger attributes.

### What the pipeline does

1. **Base setup**: Creates a PHP container with all required extensions and dependencies
   - Installs system packages (git, unzip, libzip-dev, libxml2-dev)
   - Installs PHP extensions (zip, dom, simplexml)
   - Installs Composer 1.10.22
   - Sets unlimited PHP memory for tools
   - Runs `composer install`

2. **Quality checks**:
   - PHPStan: Static analysis (level 3)
   - PHPUnit: Test suite (77 tests, 169 assertions)
   - Rector: Automated refactoring checks (optional)
   - PHP-CS-Fixer: Code style checks (optional)

## GitHub Actions

The pipeline runs automatically in GitHub Actions:
- On every push to `master`
- On every pull request
- Tests PHP 7.4 and 8.2 in parallel

See `.github/workflows/ci.yaml` for the configuration.

## Requirements

- [Dagger CLI](https://docs.dagger.io/install) installed locally
- Docker running on your machine

## Customizing the Pipeline

Edit `.dagger/src/NormalizerBundle.php` to modify the pipeline. The file uses PHP attributes to define Dagger functions:

```php
#[DaggerFunction]
#[Doc('Run PHPUnit tests')]
public function phpunit(Directory $source, string $phpVersion = '7.4'): string
{
    return $this->base($source, $phpVersion)
        ->withExec(['vendor/bin/phpunit'])
        ->stdout();
}
```

## Benefits of Dagger

- **Same pipeline locally and in CI**: No more "works on my machine"
- **Fast**: Docker layer caching speeds up repeated runs
- **Portable**: Works on any machine with Docker
- **Easy to modify**: Pure PHP code, no YAML configuration hell
- **Type-safe**: Full IDE support and type checking
