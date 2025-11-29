#!/bin/bash
# Dagger CI Helper Script
#
# This script provides easy shortcuts to run the Dagger CI pipeline locally.
#
# Usage:
#   ./dagger-ci.sh [command] [php-version]
#
# Commands:
#   test              - Run all CI checks (default)
#   phpunit           - Run only PHPUnit tests
#   phpstan           - Run only PHPStan analysis
#   rector            - Run only Rector checks
#   php-cs-fixer      - Run only PHP-CS-Fixer
#   coverage          - Run tests with coverage and export to tests/coverage/
#   test-all          - Run tests on all PHP versions (7.2, 7.4, 8.2)
#
# Examples:
#   ./dagger-ci.sh                    # Run all checks with PHP 7.4
#   ./dagger-ci.sh test 8.2           # Run all checks with PHP 8.2
#   ./dagger-ci.sh phpunit 7.4        # Run only tests with PHP 7.4
#   ./dagger-ci.sh coverage           # Generate coverage report (PHP 8.2)
#   ./dagger-ci.sh test-all           # Run tests on all PHP versions

set -e

COMMAND=${1:-test}
PHP_VERSION=${2:-7.4}

case "$COMMAND" in
  test)
    echo "Running all CI checks with PHP $PHP_VERSION..."
    dagger call test --source=. --php-version="$PHP_VERSION"
    ;;

  phpunit)
    echo "Running PHPUnit tests with PHP $PHP_VERSION..."
    dagger call phpunit --source=. --php-version="$PHP_VERSION"
    ;;

  phpstan)
    echo "Running PHPStan analysis with PHP $PHP_VERSION..."
    dagger call phpstan --source=. --php-version="$PHP_VERSION"
    ;;

  rector)
    echo "Running Rector checks with PHP $PHP_VERSION..."
    dagger call rector --source=. --php-version="$PHP_VERSION"
    ;;

  php-cs-fixer)
    echo "Running PHP-CS-Fixer with PHP $PHP_VERSION..."
    dagger call php-cs-fixer --source=. --php-version="$PHP_VERSION"
    ;;

  coverage)
    echo "Running PHPUnit with coverage (PHP 8.2)..."
    echo "Coverage reports will be saved to tests/coverage/"
    dagger call phpunit-coverage --source=. --php-version=8.2 export --path=./tests/coverage
    echo ""
    echo "✅ Coverage generated!"
    echo "   HTML report: tests/coverage/index.html"
    echo "   Clover XML:  tests/coverage/clover.xml"
    ;;

  test-all)
    echo "Running tests on all PHP versions..."
    dagger call test-matrix --source=.
    ;;

  *)
    echo "Unknown command: $COMMAND"
    echo ""
    echo "Available commands:"
    echo "  test              - Run all CI checks (default)"
    echo "  phpunit           - Run only PHPUnit tests"
    echo "  phpstan           - Run only PHPStan analysis"
    echo "  rector            - Run only Rector checks"
    echo "  php-cs-fixer      - Run only PHP-CS-Fixer"
    echo "  coverage          - Run tests with coverage (exports to tests/coverage/)"
    echo "  test-all          - Run tests on all PHP versions"
    exit 1
    ;;
esac
