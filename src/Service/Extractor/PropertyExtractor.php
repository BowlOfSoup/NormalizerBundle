<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Extractor;

class PropertyExtractor extends AbstractExtractor
{
    /** @var string */
    public const TYPE = 'property';

    /**
     * Get all properties for a given class.
     *
     * @param object|string $object
     *
     * @throws \ReflectionException
     */
    public function getProperties($object): array
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
     * Extract all annotations for a (reflected) class property.
     *
     * @param string|object $annotation
     */
    public function extractPropertyAnnotations(\ReflectionProperty $objectProperty, $annotation): array
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
}
