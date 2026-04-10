<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Model;

final class ObjectCache
{
    private static array $processedObjects = [];
    private static array $processedObjectCache = [];

    public static function hasObjectByNameAndIdentifier(string $objectName, mixed $objectIdentifier): bool
    {
        return
            array_key_exists($objectName, self::$processedObjectCache)
            && array_key_exists($objectIdentifier, self::$processedObjectCache[$objectName]);
    }

    public static function setObjectByName(string $objectName, mixed $objectIdentifier): void
    {
        self::$processedObjects[$objectName] = $objectIdentifier;
    }

    public static function setNormalizedPropertiesByNameAndIdentifier(
        string $objectName,
        mixed $objectIdentifier,
        array $normalizedProperties,
    ): void {
        self::$processedObjectCache[$objectName][$objectIdentifier] = $normalizedProperties;
    }

    public static function getObjectByNameAndIdentifier(string $objectName, mixed $objectIdentifier): mixed
    {
        return self::$processedObjectCache[$objectName][$objectIdentifier];
    }

    public static function resetObjectByNameAndIdentifier(string $objectName, mixed $objectIdentifier): void
    {
        self::$processedObjectCache[$objectName][$objectIdentifier] = [];
    }

    public static function popCache(): void
    {
        array_pop(self::$processedObjects);
    }

    public static function clear(): void
    {
        self::$processedObjects = [];
        self::$processedObjectCache = [];
    }
}
