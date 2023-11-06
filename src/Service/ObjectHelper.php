<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service;

class ObjectHelper
{
    /**
     * @param mixed $object
     *
     * @return int|string
     */
    public static function getObjectIdentifier($object)
    {
        $objectId = self::getObjectId($object);

        return $objectId ?? self::hashObject($object);
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
        } catch (\Throwable $t) {
            // For some reason this object can's be serialized.
            return null;
        }
    }
}
