<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Model;

final class ObjectCache
{
    /** @var array */
    private static $processedObjects = [];

    /** @var array */
    private static $processedObjectCache = [];

    /**
     * @param mixed $objectIdentifier
     */
    public static function hasObjectByNameAndIdentifier(string $objectName, $objectIdentifier): bool
    {
        return
            array_key_exists($objectName, static::$processedObjectCache)
            && array_key_exists($objectIdentifier, static::$processedObjectCache[$objectName]);
    }

    /**
     * @param mixed $objectIdentifier
     */
    public static function setObjectByName(string $objectName, $objectIdentifier): void
    {
        static::$processedObjects[$objectName] = $objectIdentifier;
    }

    /**
     * @param mixed $objectIdentifier
     */
    public static function setNormalizedPropertiesByNameAndIdentifier(
        string $objectName,
        $objectIdentifier,
        array $normalizedProperties
    ): void {
        static::$processedObjectCache[$objectName][$objectIdentifier] = $normalizedProperties;
    }

    /**
     * @param mixed $objectIdentifier
     *
     * @return mixed
     */
    public static function getObjectByNameAndIdentifier(string $objectName, $objectIdentifier)
    {
        return static::$processedObjectCache[$objectName][$objectIdentifier];
    }

    /**
     * @param mixed $objectIdentifier
     */
    public static function resetObjectByNameAndIdentifier(string $objectName, $objectIdentifier): void
    {
        static::$processedObjectCache[$objectName][$objectIdentifier] = [];
    }

    public static function popCache(): void
    {
        array_pop(static::$processedObjects);
    }

    public static function clear(): void
    {
        static::$processedObjects = [];
        static::$processedObjectCache = [];
    }
}
