<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonyLevelSetList;

return static function (RectorConfig $rectorConfig): void
{
    $rectorConfig->paths([
        __DIR__,
    ]);

    $rectorConfig->skip([
        __DIR__ . '/var',
        __DIR__ . '/vendor',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_72,
        SymfonyLevelSetList::UP_TO_SYMFONY_51,
    ]);

    $rectorConfig->importNames(true, false);
    $rectorConfig->importShortClasses(false);

    $rectorConfig->cacheDirectory(__DIR__ . '/var/cache/rector');
    $rectorConfig->containerCacheDirectory(__DIR__ . '/var');
};
