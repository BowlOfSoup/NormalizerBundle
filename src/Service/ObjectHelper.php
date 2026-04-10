<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service;

class ObjectHelper
{
    public static function getObjectIdentifier(mixed $object): int|string|null
    {
        $objectId = self::getObjectId($object);

        return $objectId ?? self::hashObject($object);
    }

    public static function getObjectId(mixed $object): int|string|null
    {
        $method = 'getId';
        if (is_callable([$object, $method])) {
            return $object->$method();
        }

        return null;
    }

    private static function hashObject(mixed $object, string $algorithm = 'md5'): ?string
    {
        if (!is_object($object) || $object instanceof \Closure) {
            return null;
        }

        $serializedObject = self::serializeObject($object);

        return null !== $serializedObject ? hash($algorithm, $serializedObject) : null;
    }

    /**
     * @codeCoverageIgnore
     */
    private static function serializeObject(object $object): ?string
    {
        try {
            return serialize($object);
        } catch (\Throwable) {
            // For some reason this object can's be serialized.
            return null;
        }
    }
}
