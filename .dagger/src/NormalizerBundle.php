<?php

declare(strict_types=1);

namespace DaggerModule;

use Dagger\Attribute\DaggerFunction;
use Dagger\Attribute\DaggerObject;
use Dagger\Attribute\Doc;
use Dagger\Container;
use Dagger\Directory;

use function Dagger\dag;

#[DaggerObject]
#[Doc('BowlOfSoup NormalizerBundle CI Pipeline')]
class NormalizerBundle
{
    #[DaggerFunction]
    #[Doc('Create a PHP container with dependencies installed')]
    public function base(
        #[Doc('The source directory')]
        Directory $source,
        #[Doc('PHP version to use')]
        string $phpVersion = '7.4'
    ): Container {
        return dag()
            ->container()
            ->from("php:{$phpVersion}-cli")
            ->withExec(['apt-get', 'update'])
            ->withExec([
                'apt-get',
                'install',
                '-y',
                'git',
                'unzip',
                'libzip-dev',
                'libxml2-dev',
            ])
            ->withExec(['docker-php-ext-install', 'zip', 'dom', 'simplexml'])
            ->withExec([
                'sh',
                '-c',
                'curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=1.10.22',
            ])
            ->withExec(['sh', '-c', "echo 'memory_limit = -1' > /usr/local/etc/php/conf.d/memory.ini"])
            ->withEnvVariable('COMPOSER_MEMORY_LIMIT', '-1')
            ->withMountedDirectory('/src', $source)
            ->withWorkdir('/src')
            ->withExec(['composer', 'install', '--no-interaction', '--prefer-dist']);
    }

    #[DaggerFunction]
    #[Doc('Run Rector checks (dry-run)')]
    public function rector(
        #[Doc('The source directory')]
        Directory $source,
        #[Doc('PHP version to use')]
        string $phpVersion = '7.4'
    ): string {
        return $this->base($source, $phpVersion)
            ->withExec([
                'vendor/bin/rector',
                'process',
                '--dry-run',
                '--no-progress-bar',
                '--ansi',
            ])
            ->stdout();
    }

    #[DaggerFunction]
    #[Doc('Run PHPStan static analysis')]
    public function phpstan(
        #[Doc('The source directory')]
        Directory $source,
        #[Doc('PHP version to use')]
        string $phpVersion = '7.4'
    ): string {
        return $this->base($source, $phpVersion)
            ->withExec(['vendor/bin/phpstan', 'analyze', '--no-progress', '--ansi'])
            ->stdout();
    }

    #[DaggerFunction]
    #[Doc('Run PHP-CS-Fixer checks (dry-run)')]
    public function phpCsFixer(
        #[Doc('The source directory')]
        Directory $source,
        #[Doc('PHP version to use')]
        string $phpVersion = '7.4'
    ): string {
        return $this->base($source, $phpVersion)
            ->withExec(['vendor/bin/php-cs-fixer', 'fix', '--dry-run', '--diff'])
            ->stdout();
    }

    #[DaggerFunction]
    #[Doc('Run PHPUnit tests')]
    public function phpunit(
        #[Doc('The source directory')]
        Directory $source,
        #[Doc('PHP version to use')]
        string $phpVersion = '7.4'
    ): string {
        return $this->base($source, $phpVersion)
            ->withExec(['vendor/bin/phpunit'])
            ->stdout();
    }

    #[DaggerFunction]
    #[Doc('Run PHPUnit tests with coverage and export coverage directory')]
    public function phpunitCoverage(
        #[Doc('The source directory')]
        Directory $source,
        #[Doc('PHP version to use')]
        string $phpVersion = '8.2'
    ): Directory {
        $container = $this->base($source, $phpVersion)
            ->withExec(['pecl', 'install', 'xdebug'])
            ->withExec(['docker-php-ext-enable', 'xdebug'])
            ->withEnvVariable('XDEBUG_MODE', 'coverage')
            ->withExec(['php', 'vendor/bin/phpunit']);

        return $container->directory('/src/tests/coverage');
    }

    #[DaggerFunction]
    #[Doc('Run all CI checks (PHPStan and PHPUnit)')]
    public function test(
        #[Doc('The source directory')]
        Directory $source,
        #[Doc('PHP version to use')]
        string $phpVersion = '7.4',
        #[Doc('Include Rector checks (optional, disabled by default)')]
        bool $includeRector = false
    ): string {
        $container = $this->base($source, $phpVersion);

        // Run Rector if requested
        if ($includeRector) {
            $rectorOutput = $container
                ->withExec([
                    'vendor/bin/rector',
                    'process',
                    '--dry-run',
                    '--no-progress-bar',
                    '--ansi',
                ])
                ->stdout();
        }

        // Run PHPStan
        $phpstanOutput = $container
            ->withExec(['vendor/bin/phpstan', 'analyze', '--no-progress', '--ansi'])
            ->stdout();

        // Run PHPUnit and return output
        $phpunitOutput = $container
            ->withExec(['vendor/bin/phpunit'])
            ->stdout();

        return "✅ All CI checks passed!\n\n" . $phpunitOutput;
    }

    #[DaggerFunction]
    #[Doc('Run CI for multiple PHP versions')]
    public function testMatrix(
        #[Doc('The source directory')]
        Directory $source
    ): string {
        $versions = ['7.2', '7.4', '8.2'];
        $results = [];

        foreach ($versions as $version) {
            try {
                $this->test($source, $version);
                $results[] = "✅ PHP {$version}: PASSED";
            } catch (\Exception $e) {
                $results[] = "❌ PHP {$version}: FAILED";
                throw $e;
            }
        }

        return implode("\n", $results);
    }
}
