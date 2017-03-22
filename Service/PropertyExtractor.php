<?php

namespace BowlOfSoup\NormalizerBundle\Service;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Proxy;
use Exception;
use ReflectionProperty;

class PropertyExtractor
{
    /** @var string */
    const TYPE = 'property';

    /** @var bool */
    const FORCE_PROPERTY_GET_METHOD = true;

    /** @var Reader */
    protected $annotationReader;

    /**
     * @param \Doctrine\Common\Annotations\Reader $annotationReader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Extract all annotations for a (reflected) class property.
     *
     * @param \ReflectionProperty $objectProperty
     * @param string              $annotation
     *
     * @return array
     */
    public function extractPropertyAnnotations(ReflectionProperty $objectProperty, $annotation)
    {
        $annotations = array();

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
     * @param object             $object
     * @param ReflectionProperty $property
     * @param bool               $forceGetMethod
     *
     * @throws Exception
     *
     * @return mixed|null
     */
    public function getPropertyValue(
        $object,
        ReflectionProperty $property,
        $forceGetMethod = false
    ) {
        $propertyName = $property->getName();

        if ('id' !== $propertyName && $object instanceof Proxy) {
            // Force initialization of Doctrine proxy.
            $forceGetMethod = true;
        }

        if (true === $forceGetMethod || !property_exists($object, $propertyName)) {
            $getMethodName = 'get' . ucfirst($propertyName);
            if (!is_callable(array($object, $getMethodName))) {
                throw new Exception(
                    'Unable to get property value. No get() method found for property ' . $propertyName
                );
            }

            return $object->$getMethodName();
        }

        return $property->getValue($object);
    }

    /**
     * Returns a value by specified method.
     *
     * @param object $object
     * @param string $method
     *
     * @return mixed
     */
    public function getPropertyValueByMethod($object, $method)
    {
        if (is_callable(array($object, $method))) {
            return $object->$method();
        }

        return null;
    }

    /**
     * Gets the id from an object if available through getter.
     *
     * @param object $object
     *
     * @return string|int|null
     */
    public function getId($object)
    {
        return $this->getPropertyValueByMethod($object, 'getId');
    }
}
