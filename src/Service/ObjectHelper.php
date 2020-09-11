<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service;

class ObjectHelper
{
    /**
     * @param object $object
     *
     * @return int|string
     */
    public static function getObjectIdentifier($object)
    {
        $objectId = self::getObjectId($object);

        return null === $objectId ? static::hashObject($object) : $objectId;
    }

    /**
     * @param object $object
     *
     * @return int|string|null
     */
    public static function getObjectId($object)
    {
        $method = 'getId';
        if (is_callable([$object, $method])) {
            return $object->$method();
        }

        return null;
    }

    /**
     * @param object $object
     */
    private static function hashObject($object, string $algorithm = 'md5'): ?string
    {
        if (!is_object($object) || $object instanceof \Closure) {
            return null;
        }

        try {
            $serializedObject = serialize($object);
        } catch (\Throwable $t) {
            // For some reason this object can's be serialized.
            return null;
        }

        return hash($algorithm, $serializedObject);
    }
}
