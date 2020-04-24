<?php

namespace BowlOfSoup\NormalizerBundle\Service;

use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Persistence\Proxy;
use ReflectionException;
use ReflectionProperty;

class PropertyExtractor
{
    /** @var string */
    public const TYPE = 'property';

    /** @var bool */
    public const FORCE_PROPERTY_GET_METHOD = true;

    /** @var \Doctrine\Common\Annotations\Reader */
    protected $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Extract all annotations for a (reflected) class property.
     *
     * @param string|object $annotation
     */
    public function extractPropertyAnnotations(ReflectionProperty $objectProperty, $annotation): array
    {
        $annotations = [];

        $propertyAnnotations = $this->annotationReader->getPropertyAnnotations($objectProperty);
        foreach ($propertyAnnotations as $propertyAnnotation) {
            if ($propertyAnnotation instanceof $annotation) {
                $annotations[] = $propertyAnnotation;
            }
        }

        return $annotations;
    }

    /**
     * Returns a value for a (reflected) property.
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     *
     * @return mixed|null
     */
    public function getPropertyValue(
        object $object,
        ReflectionProperty $property,
        bool $forceGetMethod = false
    ) {
        $propertyName = $property->getName();
        $propertyValue = null;
        try {
            $propertyValue = $property->getValue($object);
        } catch (ReflectionException $e) {
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

            if (null !== $propertyValue) {
                return $propertyValue;
            }

            if ($object instanceof Proxy) {
                throw new BosNormalizerException('Unable to initiate Doctrine proxy, not get() method found for property ' . $propertyName);
            }

            throw new BosNormalizerException('Unable to get property value. No get() method found for property ' . $propertyName);
        }

        return $propertyValue;
    }

    /**
     * Returns a value by specified method.
     *
     * @return mixed
     */
    public function getPropertyValueByMethod(object $object, string $method)
    {
        if (is_callable([$object, $method])) {
            return $object->$method();
        }

        return null;
    }

    /**
     * Gets the id from an object if available through getter.
     *
     * @return string|int|null
     */
    public function getId(object $object)
    {
        return $this->getPropertyValueByMethod($object, 'getId');
    }
}
