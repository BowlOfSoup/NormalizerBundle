<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Extractor;

use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use Doctrine\Persistence\Proxy;

class PropertyExtractor
{
    public const bool GET_ONLY_PRIVATES = true;
    public const string TYPE = 'property';

    /**
     * Get all properties for a given class.
     */
    public function getProperties(object|string $object): array
    {
        if (!is_object($object)) {
            return [];
        }

        $reflectedClass = new \ReflectionClass($object);
        $classProperties = $this->getClassProperties($reflectedClass);

        // Also get (private) variables from parent class.
        $privateProperties = [];
        while ($reflectedClass = $reflectedClass->getParentClass()) {
            $privateProperties[] = $this->getClassProperties($reflectedClass, static::GET_ONLY_PRIVATES);
        }

        return array_merge($classProperties, ...$privateProperties);
    }

    /**
     * Get class properties through reflection.
     *
     * @return \ReflectionProperty[]
     */
    private function getClassProperties(\ReflectionClass $reflectedClass, bool $onlyPrivates = false): array
    {
        if ($onlyPrivates) {
            return $reflectedClass->getProperties(\ReflectionProperty::IS_PRIVATE);
        }

        return $reflectedClass->getProperties(
            \ReflectionProperty::IS_PUBLIC |
            \ReflectionProperty::IS_PROTECTED |
            \ReflectionProperty::IS_PRIVATE
        );
    }

    /**
     * Returns a value for a (reflected) property.
     *
     * @throws BosNormalizerException
     */
    public function getPropertyValue(object $object, \ReflectionProperty $property): mixed
    {
        $propertyName = $property->getName();
        $propertyValue = null;
        $forceGetMethod = false;

        try {
            $propertyValue = $property->getValue($object);
        } catch (\ReflectionException) {
            $forceGetMethod = true;
        }

        if ($object instanceof Proxy) {
            // Force initialization of Doctrine proxy.
            $forceGetMethod = true;
        }

        if (true === $forceGetMethod || !property_exists($object, $propertyName)) {
            $getMethodName = 'get' . ucfirst($propertyName);
            if (is_callable([$object, $getMethodName])) {
                return $object->$getMethodName();
            }

            if ($object instanceof Proxy) {
                throw new BosNormalizerException('Unable to initiate Doctrine proxy, not get() method found for property ' . $propertyName);
            }
        }

        return $propertyValue;
    }

    /**
     * Returns a value by specified method.
     */
    public function getPropertyValueByMethod(object $object, string $method): mixed
    {
        if (is_callable([$object, $method])) {
            return $object->$method();
        }

        return null;
    }
}
